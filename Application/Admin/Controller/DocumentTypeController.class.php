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
 * 后台类型控制器
 * @author jry <598821125@qq.com>
 */
class DocumentTypeController extends AdminController{
    /**
     * 类型列表
     * @author jry <598821125@qq.com>
     */
    public function index(){
        //搜索
        $keyword = (string)I('keyword');
        $condition = array('like','%'.$keyword.'%');
        $map['id|title|name'] = array($condition, $condition, $condition,'_multi'=>true);

        //获取所有类型
        $map['system'] = array('eq', '0'); //非系统类型
        $map['status'] = array('egt', '0'); //禁用和正常状态
        $data_list = D('DocumentType')->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))->where($map)->order('sort asc,id asc')->select();
        $page = new \Common\Util\Page(D('DocumentType')->where($map)->count(), C('ADMIN_PAGE_ROWS'));

        $attr['title'] = '字段管理';
        $attr['class'] = 'label label-success';
        $attr['href']  = U('Admin/DocumentAttribute/index', array('doc_type' => '__data_id__'));

        //使用Builder快速建立列表页面。
        $builder = new \Common\Builder\ListBuilder();
        $builder->setMetaTitle('类型列表')  //设置页面标题
                ->addTopButton('addnew') //添加新增按钮
                ->addTopButton('resume') //添加启用按钮
                ->addTopButton('forbid') //添加禁用按钮
                ->setSearch('请输入ID/类型标题', U('index'))
                ->addTableColumn('id', 'ID')
                ->addTableColumn('icon', '图标', 'icon')
                ->addTableColumn('name', '名称')
                ->addTableColumn('title', '标题')
                ->addTableColumn('ctime', '创建时间', 'time')
                ->addTableColumn('sort', '排序')
                ->addTableColumn('status', '状态', 'status')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataList($data_list)    //数据列表
                ->setTableDataPage($page->show()) //数据列表分页
                ->addRightButton('self', $attr) //添加字段管理按钮
                ->addRightButton('edit')   //添加编辑按钮
                ->addRightButton('forbid') //添加禁用/启用按钮
                ->addRightButton('delete') //添加删除按钮
                ->display();
    }

    /**
     * 新增类型
     * @author jry <598821125@qq.com>
     */
    public function add(){
        if(IS_POST){
            $document_type_object = D('DocumentType');
            $data = $document_type_object->create();
            if($data){
                $id = $document_type_object->add();
                if($id){
                    $this->success('新增成功', U('index'));
                }else{
                    $this->error('新增失败');
                }
            }else{
                $this->error($document_type_object->getError());
            }
        }else{
            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('新增类型')  //设置页面标题
                    ->setPostUrl(U('add')) //设置表单提交地址
                    ->addFormItem('name', 'text', '类型名称', '类型名称')
                    ->addFormItem('title', 'text', '类型标题', '类型标题')
                    ->addFormItem('icon', 'icon', '图标', '类型图标')
                    ->addFormItem('sort', 'num', '排序', '用于显示的顺序')
                    ->display();
        }
    }

    /**
     * 编辑类型
     * @author jry <598821125@qq.com>
     */
    public function edit($id){
        if(IS_POST){
            $document_type_object = D('DocumentType');
            $data = $document_type_object->create();
            if($data){
                if($document_type_object->save()!== false){
                    $this->success('更新成功', U('index'));
                }else{
                    $this->error('更新失败');
                }
            }else{
                $this->error($document_type_object->getError());
            }
        }else{
            $document_type_info = D('DocumentType')->find($id);
            $document_type_field_group = parse_attr($document_type_info['field_group']);
            $document_type_field_sort = json_decode($document_type_info['field_sort'], true);

            //获取文档字段
            $map['status'] = array('eq', '1');
            $map['show'] = array('eq', '1');
            $map['doc_type'] = array('in', '0,'.$id);
            $attribute_list = D('DocumentAttribute')->where($map)->select();

            //解析字段
            $new_attribute_list = array();
            foreach($attribute_list as $attr){
                $new_attribute_list[$attr['id']] = $attr['title'];
            }

            //构造拖动排序options
            foreach($document_type_field_sort as $key => $val){
                $field[$key]['title'] = $document_type_field_group[$key];
                $temp = array();
                foreach($val as $val2){
                    if($new_attribute_list[$val2]){
                        $temp[$val2] = $new_attribute_list[$val2];
                    }
                    unset($new_attribute_list[$val2]);
                }
                $field[$key]['field'] = $temp;
            }

            //计算未排序字段分组的key
            $unsort_key = array_pop(array_keys($field)) + 1;

            //未排序字段
            if($new_attribute_list){
                $field[$unsort_key]['title'] = "未排序";
                $field[$unsort_key]['field'] = $new_attribute_list;
            }

            //使用FormBuilder快速建立表单页面。
            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('编辑类型')  //设置页面标题
                    ->setPostUrl(U('edit')) //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '类型名称', '类型名称')
                    ->addFormItem('title', 'text', '类型标题', '类型标题')
                    ->addFormItem('field_group', 'textarea', '字段分组', '字段分组')
                    ->addFormItem('icon', 'icon', '图标', '类型图标')
                    ->addFormItem('sort', 'num', '排序', '用于显示的顺序')
                    ->addFormItem('field_sort', 'board', '字段排序', '字段排序', $field)
                    ->setFormData(D('DocumentType')->find($id))
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
        $map['id'] = array('in',$ids);
        switch($status){
            case 'delete'  : //删除条目
                $con['doc_type'] = $ids;
                $count = D('category')->where($con)->count();
                if($count > 0){
                    $this->error('存在该文档类型的分类，无法删除！');
                }else{
                    $result = D('DocumentType')->where($map)->delete();
                    if($result){
                        $this->success('删除成功，不可恢复！');
                    }else{
                        $this->error('删除失败');
                    }
                }
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}
