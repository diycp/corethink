<?php
// +----------------------------------------------------------------------
// | CoreThink [ Simple Efficient Excellent ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.corethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: jry <598821125@qq.com> <http://www.corethink.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;
/**
 * 后台文章控制器
 * @author jry <598821125@qq.com>
 */
class DocumentController extends AdminController{
    /**
     * 默认方法
     * @author jry <598821125@qq.com>
     */
    public function index($cid = null){
        //搜索
        $keyword = (string)I('keyword');
        $condition = array('like','%'.$keyword.'%');
        $map['id|title'] = array($condition, $condition,'_multi'=>true);

        if($cid){
            $map['cid'] = $cid;
            $category = D('Category')->find($cid);
        }
        $map['status'] = array('egt', 0);
        $document_list = D('Document')->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))
                                      ->order('sort desc,id desc')->where($map)->select();
        $page = new \Common\Util\Page(D('Document')->where($map)->count(), C('ADMIN_PAGE_ROWS'));

        //移动按钮属性
        $move_attr['title'] = '移 动';
        $move_attr['class'] = 'btn btn-info';
        $move_attr['onclick'] = 'move()';

        //构造移动文档所需内容
        $map = array();
        $map['status'] = array('eq', 1);
        $map['doc_type'] = array('eq', $category['doc_type']); //文档类型相同的分类才能移动
        $category_list = D('Category')->where($map)->select();
        $tree = new \Common\Util\Tree();
        $category_list = $tree->toFormatTree($category_list);
        //构造移动文档的目标分类列表
        $options = '';
        foreach($category_list as $key => $val){
            $options .= '<option value="'.$val['id'].'">'.$val['title_show'].'</option>';
        }

        $extra_html = <<<EOF
        <div class="modal fade" id="moveModal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                        <p class="modal-title">移动至</p>
                    </div>
                    <div class="modal-body">
                        <form action="{:U('Document/move')}" method="post" class="form">
                            <div class="form-group">
                                <select name="to_cid" class="form-control">{$options}</select>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="ids">
                                <input type="hidden" name="from_cid" value="{$cid}">
                                <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form">确 定</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function move(){
                var ids = '';
                $('input[name="ids[]"]:checked').each(function(){
                   ids += ',' + $(this).val();
                });
                if(ids != ''){
                    ids = ids.substr(1);
                    $('input[name="ids"]').val(ids);
                    $('.modal-title').html('移动选中的的文章至：');
                    $('#moveModal').modal('show', 'fit')
                }else{
                    $.bootstrapGrowl('请选择需要移动的文章', {
                        type: 'danger',
                        align: 'center',
                        width: 'auto',
                    });
                }
            }
        </script>
EOF;

        //使用Builder快速建立列表页面。
        $builder = new \Common\Builder\ListBuilder();
        $builder->setMetaTitle($category['title']) //设置页面标题
                ->addTopButton('addnew', array('href' => U('add', array('cid' => $cid)))) //添加新增按钮
                ->addTopButton('resume')  //添加启用按钮
                ->addTopButton('forbid')  //添加禁用按钮
                ->addTopButton('recycle') //添加回收按钮
                ->addTopButton('self', $move_attr) //添加移动按钮
                ->setSearch('请输入ID/标题', U('index'))
                ->addTableColumn('id', 'ID')
                ->addTableColumn('title', '标题')
                ->addTableColumn('ctime', '发布时间', 'time')
                ->addTableColumn('sort', '排序', 'text')
                ->addTableColumn('status', '状态', 'status')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataList($document_list) //数据列表
                ->setTableDataPage($page->show())  //数据列表分页
                ->addRightButton('edit')    //添加编辑按钮
                ->addRightButton('forbid')  //添加禁用/启用按钮
                ->addRightButton('recycle') //添加回收按钮
                ->setExtraHtml($extra_html)
                ->display();
    }

    /**
     * 新增文档
     * @author jry <598821125@qq.com>
     */
    public function add(){
        if(IS_POST){
            //新增文档
            $document_object = D('Document');
            $result = $document_object->update();
            if(!$result){
                $this->error($document_object->getError());
            }else{
                $this->success('新增成功', U('Document/index', array('cid' => I('post.cid'))));
            }
        }else{
            //获取当前分类
            $cid = I('get.cid');
            $category_info = D('Category')->find($cid);
            $doc_type = D('DocumentType')->find($category_info['doc_type']);
            $field_sort = json_decode($doc_type['field_sort'], true);
            $field_group = parse_attr($doc_type['field_group']);

            //获取文档字段
            $map['status'] = array('eq', '1');
            $map['show'] = array('eq', '1');
            $map['doc_type'] = array('in', '0,'.$category_info['doc_type']);
            $attribute_list = D('DocumentAttribute')->where($map)->select();

            //解析字段options
            $new_attribute_list = array();
            foreach($attribute_list as $attr){
                if($attr['name'] == 'cid'){
                    $con['group'] = $category_info['group'];
                    $con['doc_type'] = $category_info['doc_type'];
                    $con['status'] = array('egt', 0);
                    $attr['value'] = $cid;
                    $attr['options'] = $this->selectListAsTree('Category', $con);
                }else{
                    $attr['options'] = parse_attr($attr['options']);
                }
                $new_attribute_list[$attr['id']] = $attr;
            }

            //表单字段排序及分组
            if($field_sort){
                $new_attribute_list_sort = array();
                foreach($field_sort as $k1 => &$v1){
                    $new_attribute_list_sort[0]['type'] = 'group';
                    $new_attribute_list_sort[0]['options']['group'.$k1]['title'] = $field_group[$k1];
                    foreach($v1 as $k2 => $v2){
                        $new_attribute_list_sort[0]['options']['group'.$k1]['options'][] = $new_attribute_list[$v2];
                    }
                }
                $new_attribute_list = $new_attribute_list_sort;
            }

            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('新增文章') //设置页面标题
                    ->setPostUrl(U('add')) //设置表单提交地址
                    ->addFormItem('doc_type', 'hidden')
                    ->setFormData(array('doc_type' => $category_info['doc_type']))
                    ->setExtraItems($new_attribute_list)
                    ->display();
        }
    }

    /**
     * 编辑文章
     * @author jry <598821125@qq.com>
     */
    public function edit($id){
        if(IS_POST){
            //更新文档
            $document_object = D('Document');
            $result = $document_object->update();
            if(!$result){
                $this->error($document_object->getError());
            }else{
                $this->success('更新成功', U('Document/index', array('cid' => I('post.cid'))));
            }
        }else{
            //获取文档信息
            $document_info = D('Document')->detail($id);

            //获取当前分类
            $category_info = D('Category')->find($document_info['cid']);
            $doc_type = D('DocumentType')->find($category_info['doc_type']);
            $field_sort = json_decode($doc_type['field_sort'], true);
            $field_group = parse_attr($doc_type['field_group']);

            //获取文档字段
            $map['status'] = array('eq', '1');
            $map['show'] = array('eq', '1');
            $map['doc_type'] = array('in', '0,'.$category_info['doc_type']);
            $attribute_list = D('DocumentAttribute')->where($map)->select();

            //解析字段options
            $new_attribute_list = array();
            foreach($attribute_list as $attr){
                if($attr['name'] == 'cid'){
                    $con['group'] = $category_info['group'];
                    $con['doc_type'] = $category_info['doc_type'];
                    $con['status'] = array('egt', 0);
                    $attr['options'] = $this->selectListAsTree('Category', $con);
                }else{
                    $attr['options'] = parse_attr($attr['options']);
                }
                $new_attribute_list[$attr['id']] = $attr;
                $new_attribute_list[$attr['id']]['value'] = $document_info[$attr['name']];
            }

            //表单字段排序及分组
            if($field_sort){
                $new_attribute_list_sort = array();
                foreach($field_sort as $k1 => &$v1){
                    $new_attribute_list_sort[0]['type'] = 'group';
                    $new_attribute_list_sort[0]['options']['group'.$k1]['title'] = $field_group[$k1];
                    foreach($v1 as $k2 => $v2){
                        $new_attribute_list_sort[0]['options']['group'.$k1]['options'][] = $new_attribute_list[$v2];
                    }
                }
                $new_attribute_list = $new_attribute_list_sort;
            }

            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('编辑文章') //设置页面标题
                    ->setPostUrl(U('edit')) //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->setExtraItems($new_attribute_list)
                    ->setFormData($document_info)
                    ->display();
        }
    }

    /**
     * 移动文档
     * @author jry <598821125@qq.com>
     */
    public function move(){
        if(IS_POST){
            $ids = I('post.ids');
            $from_cid = I('post.from_cid');
            $to_cid = I('post.to_cid');
            if($from_cid === $to_cid){
                $this->error('目标分类与当前分类相同');
            }
            if($to_cid){
                $category_model = D('Category');
                $form_category_type = $category_model->getFieldById($from_cid, 'doc_type');
                $to_category_type = $category_model->getFieldById($to_cid, 'doc_type');
                if($form_category_type === $to_category_type){
                    $map['id'] = array('in',$ids);
                    $data = array('cid' => $to_cid);
                    $this->editRow('Document', $data, $map, array('success'=>'移动成功','error'=>'移动失败'));
                }else{
                    $this->error('该分类模型不匹配');
                }
            }else{
                $this->error('请选择目标分类');
            }
        }
    }

    /**
     * 回收站
     * @author jry <598821125@qq.com>
     */
    public function recycle(){
        $map['status'] = array('eq', '-1');
        $document_list = D('Document')->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))->where($map)->select();
        $page = new \Common\Util\Page(D('Document')->where($map)->count(), C('ADMIN_PAGE_ROWS'));

        //使用Builder快速建立列表页面。
        $builder = new \Common\Builder\ListBuilder();
        $builder->setMetaTitle('回收站') //设置页面标题
                ->addTopButton('delete') //添加删除按钮
                ->addTopButton('restore') //添加还原按钮
                ->setSearch('请输入ID/文档名称', U('recycle'))
                ->addTableColumn('id', 'ID')
                ->addTableColumn('title', '标题')
                ->addTableColumn('ctime', '发布时间', 'time')
                ->addTableColumn('sort', '排序')
                ->addTableColumn('status', '状态', 'status')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataList($document_list) //数据列表
                ->setTableDataPage($page->show()) //数据列表分页
                ->addRightButton('forbid') //添加禁用/启用按钮
                ->addRightButton('delete') //添加删除按钮
                ->display();
    }
}
