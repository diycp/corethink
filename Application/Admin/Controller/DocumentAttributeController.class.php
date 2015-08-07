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
class DocumentAttributeController extends AdminController{
    //改变表单类型的时候同时改变字段定义
    private $extra_html = <<<EOF
        <script type="text/javascript">
            //改变表单类型的时候同时改变字段定义
            $(function(){
                $('.item_type select').change(function(){
                    var curren_name = $(this).find('option:selected').attr('value');
                    var data_field  = $(this).find('option:selected').attr('data-field');
                    $('.item_field input').val(data_field);
                });
            });
        </script>
EOF;

    /**
     * 默认方法
     * @author jry <598821125@qq.com>
     */
    public function index($doc_type){
        //搜索
        $keyword = (string)I('keyword');
        $condition = array('like','%'.$keyword.'%');
        $map['id|name|title'] = array($condition, $condition, $condition,'_multi'=>true);

        if($doc_type){
            $map['doc_type'] = $doc_type;
        }
        $map['status'] = array('egt', 0);
        $document_attribute_list = D('DocumentAttribute')->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))
                                                         ->order('id desc')->where($map)->select();
        $page = new \Common\Util\Page(D('DocumentAttribute')->where($map)->count(), C('ADMIN_PAGE_ROWS'));

        $attr['title'] = '新 增';
        $attr['class'] = 'btn btn-primary';
        $attr['href'] = U('add', array('doc_type' => $doc_type));

        //使用Builder快速建立列表页面。
        $builder = new \Common\Builder\ListBuilder();
        $builder->setMetaTitle('字段管理') //设置页面标题
                ->addTopButton('self', $attr) //添加新增按钮
                ->addTopButton('resume') //添加启用按钮
                ->addTopButton('forbid') //添加禁用按钮
                ->setSearch('请输入ID/名称/标题', U('index'))
                ->addTableColumn('id', 'ID')
                ->addTableColumn('name', '名称')
                ->addTableColumn('title', '标题')
                ->addTableColumn('type', '类型', 'type')
                ->addTableColumn('ctime', '发布时间', 'time')
                ->addTableColumn('status', '状态', 'status')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataList($document_attribute_list) //数据列表
                ->setTableDataPage($page->show()) //数据列表分页
                ->addRightButton('edit')   //添加编辑按钮
                ->addRightButton('forbid') //添加禁用/启用按钮
                ->addRightButton('delete') //添加删除按钮
                ->display();
    }

    /**
     * 新增字段
     * @author jry <598821125@qq.com>
     */
    public function add($doc_type){
        if(IS_POST){
            $document_attribute_object = D('DocumentAttribute');
            $data = $document_attribute_object->create();
            if($data){
                $id = $document_attribute_object->add();
                if($id){
                    $result = $document_attribute_object->addField($data); //新增表字段
                    if($result){
                        $this->success('新增字段成功', U('index', array('doc_type' => I('doc_type'))));
                    }else{
                        $document_attribute_object->delete($id); //删除新增数据
                        $this->error('新建字段出错！');
                    }
                }else{
                    $this->error('新增字段出错！');
                }
            }else{
                $this->error($document_attribute_object->getError());
            }
        }else{
            //表单默认值
            $info['doc_type'] = $doc_type;
            $info['show'] = 1;

            //获取Builder表单类型转换成一维数组
            $form_item_type = C('FORM_ITEM_TYPE');
            foreach($form_item_type as $key => $val){
                $new_form_item_type[$key]['title']      = $val[0];
                $new_form_item_type[$key]['data-field'] = $val[1];
            }

            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('新增字段') //设置页面标题
                    ->setPostUrl(U('add')) //设置表单提交地址
                    ->addFormItem('doc_type', 'select', '文档类型', '文档类型', $this->selectListAsTree('DocumentType'))
                    ->addFormItem('name', 'text', '字段名称', '字段名称，如“title”')
                    ->addFormItem('title', 'text', '字段标题', '字段标题，如“标题”')
                    ->addFormItem('type', 'select', '字段类型', '字段类型', $new_form_item_type)
                    ->addFormItem('field', 'text', '字段定义', '字段定义，如：int(11) unsigned NOT NULL ')
                    ->addFormItem('value', 'text', '字段默认值', '字段默认值')
                    ->addFormItem('show', 'radio', '是否显示', '是否显示', array('1' => '显示', '0' => '不显示'))
                    ->addFormItem('options', 'textarea', '额外选项', '额外选项radio/select等需要配置此项')
                    ->addFormItem('tip', 'textarea', '字段补充说明', '字段补充说明')
                    ->setFormData($info)
                    ->setExtraHtml($this->extra_html)
                    ->display();
        }
    }

    /**
     * 编辑分类
     * @author jry <598821125@qq.com>
     */
    public function edit($id){
        if(IS_POST){
            $document_attribute_object = D('DocumentAttribute');
            $data = $document_attribute_object->create();
            if($data){
                $result = $document_attribute_object->updateField($data); //更新字段
                if($result){
                    $status = $document_attribute_object->save(); //更新字段信息
                    if($status){
                        $this->success('更新字段成功', U('index', array('doc_type' => I('doc_type'))));
                    }else{
                        $this->error('更新属性出错！');
                    }
                }else{
                    $this->error('更新字段出错！');
                }
            }else{
                $this->error($document_attribute_object->getError());
            }
        }else{
            //获取Builder表单类型转换成一维数组
            $form_item_type = C('FORM_ITEM_TYPE');
            foreach($form_item_type as $key => $val){
                $new_form_item_type[$key]['title']      = $val[0];
                $new_form_item_type[$key]['data-field'] = $val[1];
            }

            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('编辑字段') //设置页面标题
                    ->setPostUrl(U('edit')) //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('doc_type', 'select', '文档类型', '文档类型', $this->selectListAsTree('DocumentType'))
                    ->addFormItem('name', 'text', '字段名称', '字段名称，如“title”')
                    ->addFormItem('title', 'text', '字段标题', '字段标题，如“标题”')
                    ->addFormItem('type', 'select', '字段类型', '字段类型', $new_form_item_type)
                    ->addFormItem('field', 'text', '字段定义', '字段定义，如：int(11) NOT NULL ')
                    ->addFormItem('value', 'text', '字段默认值', '字段默认值')
                    ->addFormItem('show', 'radio', '是否显示', '是否显示', array('1' => '显示', '0' => '不显示'))
                    ->addFormItem('options', 'textarea', '额外选项', '额外选项radio/select等需要配置此项')
                    ->addFormItem('tip', 'textarea', '字段补充说明', '字段补充说明')
                    ->setFormData(D('DocumentAttribute')->find($id))
                    ->setExtraHtml($this->extra_html)
                    ->display();
        }
    }

    /**
     * 设置一条或者多条数据的状态
     * @author jry <598821125@qq.com>
     */
    public function setStatus($model = CONTROLLER_NAME){
        $ids    = I('request.ids');
        $status = I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        switch($status){
            case 'delete' : //删除条目
                $document_attribute_object = D('DocumentAttribute');
                $field = $document_attribute_object->find($ids);
                $result1 = $document_attribute_object->delete($ids);
                $result2 = $document_attribute_object->deleteField($field);
                if($result1 && $result2){
                    $this->success('删除成功，不可恢复！');
                }else{
                    $this->error('删除失败'.$document_attribute_object->getError());
                }
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }

    /**
     * 根据表单类型名称获取字段定义
     * @author jry <598821125@qq.com>
     */
    public function getFieldByName($name){
        $form_item_type = C('FORM_ITEM_TYPE');
        if($name){
             $this->success($form_item_type[$name][1]);
        }else{
            $this->error('获取字段定义失败');
        }
    }
}
