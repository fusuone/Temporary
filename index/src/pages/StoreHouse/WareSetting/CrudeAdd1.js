import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Icon, Modal, Cascader, message, Input, Row, Col, Form, Radio, Upload } from 'antd';
import moment from 'moment';
import http from '@/utils/http';
import SelectCustomer from '@/cps/SelectComponents/SelectCustomer';
import Api from '@/common/api';

function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}
const options = [
  {
    value: '0',
    label: '百货商品',
    children: [
      {value: '01',label: '糖巧果冻',isLeaf: false},
      {value: '02',label: '饼干糕点',isLeaf: false},
      {value: '03',label: '麻辣素食',isLeaf: false},
      {value: '04',label: '肉干肉脯',isLeaf: false},
      {value: '05',label: '坚果蜜饯',isLeaf: false},
      {value: '06',label: '膨化食品',isLeaf: false},
      {value: '07',label: '玩具食品',isLeaf: false},
      {value: '08',label: '冲调保健',isLeaf: false},
      {value: '09',label: '纸品湿巾',isLeaf: false},
      {value: '010',label: '家庭清洁',isLeaf: false},
      {value: '011',label: '洗发沐浴',isLeaf: false},
      {value: '012',label: '口腔护理',isLeaf: false},
      {value: '013',label: '女性护理',isLeaf: false},
      {value: '014',label: '护肤计生',isLeaf: false},
      {value: '015',label: '母婴玩具',isLeaf: false},
      {value: '016',label: '居家百货',isLeaf: false},
      {value: '017',label: '家装服饰',isLeaf: false},
      {value: '018',label: '办公用品',isLeaf: false},
    ],
  },
  {
    value: '1',
    label: '酒水饮料',
    children: [
      { value: '11', label: '饮料', isLeaf: false },
      { value: '12', label: '啤酒', isLeaf: false },
      { value: '13', label: '奶制品', isLeaf: false },
      { value: '14', label: '白酒', isLeaf: false },
      { value: '15', label: '葡萄酒', isLeaf: false },
      { value: '16', label: '其它酒类', isLeaf: false },
      { value: '17', label: '方便速食', isLeaf: false },
      { value: '18', label: '米面粮油', isLeaf: false },
      { value: '19', label: '厨房调味', isLeaf: false },
    ],
  },
];
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CrudeAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,//提交动画
      selectWorkerType: '',
      showSelectDepot: false, //显示仓库
      showSelectModel: false,//显示工艺规格
      showSelectWorker: false,//显示电话
      showSelectFactory: false,//选择加工工厂
      showSelectCustomer: false,//选择客户
      showSelectGecarplate:false,//选择车辆
      EditModalVisible: false,
      EditModalData: {},
      previewVisible: false,//预览可见
      previewImage: '',//base64地址
      fileList: [],//所有图片地址
      fileList1: [],//第一张
      fileImage1:[],
      fileImage: [],//最终地址
      info: {}, // 商城信息
      modalVisible: false, // modal
      modalData: {},
      admin: props.currentUser.admin,
      username: props.currentUser.username, // 操作员
      admincode:props.currentUser.admincode,
      image1: '',
      image2: '',
      image3: '',
      image4: '',
      image5: '',
      image6: '',
      waresname: '', // 名称
      model: '', // 型号
      productno: '', // 编码
      price: '', //
      mallprice: '', // 商城的单价
      unit: '', // 单位
      describe: '', // 描述
      billno: '', // 有值表示编辑
      checkedIndex: '',
      series: '',
      brand: '', // 品牌
      category: '',
      catname: '',
      options
    };
  }
//判断是否从编辑点入
  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      if (nextProps.addOrEdit == '1') {
        this.setFieldsValue(nextProps);
      }else{
        this.props.form.setFieldsValue({
          category:[""]
        })
      }
    }
  }
  下拉列表变化时触发
  onChange =async(value, selectedOptions) => {
    if(selectedOptions.length==3){
      await this.setState({
        category1:selectedOptions[0].value,
        category2:selectedOptions[1].value,
        category3:selectedOptions[2].value
      })
    }
  };
  // 展示
  displayRender=(label)=>{
    if(label.length==3){
      this.state.EditModalData.catname=label[2]
    }
    return this.state.EditModalData.catname==null ?label.length==3?label[2]:null:this.state.EditModalData.catname
  }
  //动态加载
  loadData = (selectedOptions) => {
    const targetOption = selectedOptions[selectedOptions.length - 1];
    http({
      method: 'get',
      api: 'get_mall_category_app',
      params: {
        flag:targetOption.value
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        message.info('成功');
      } else {
        message.info(msg);
      }this.setState( { 
        submitting: false,
        moudelDate:data.list
       });
       }).catch(() => {
         this.setState({ submitting: false });
       });
    targetOption.loading = true;
    setTimeout(() => {
      targetOption.loading = false;
      var s={}
      targetOption.children=[]
      if(this.state.moudelDate.length){
      for(var a=0;a<this.state.moudelDate.length;a++){
         s={
          label: `${this.state.moudelDate[a].typename} `,
          value: this.state.moudelDate[a].billno,
        },
        targetOption.children.push(s)
      } 
    }else{
    }
      this.setState({
        options: [...this.state.options],
      });
    }, 1000);
  };
  setFormData = (newState) => {
    this.setState(state => ({
      formData: {
        ...state.formData,
        ...newState
      }
    }));
  }

  showModal = () => {
    this.setState({
      modalVisible: true
    });
  };
  // 是否显示预览
  handleCancelPreview = async (file) => {
    this.setState({ previewVisible: false });
  }
  //预览
  handlePreview = async (file,previewImage) => {
    if (!file.url && !file.preview) {
      file.preview = await getBase64(file.originFileObj);
    }
    this.setState({
      previewImage: file.url || file.preview,
      previewVisible: true
    });
  };
  handleChangePreview1 = ({fileList}) => {
    const fileImageArray = [];
    for (let i = 0; i < fileList.length; i++) {
      let a = '';
      if (fileList[i].response) {
        a = fileList[i].response.data.source;
      } else {
        a = fileList[i].url
      }
      fileImageArray[i] = a;//a 图片url地址
    }
    setTimeout(() => {
      this.setState({
        fileImage1: fileImageArray
      });
    }, 4000);
    this.setState({
      fileList1:fileList
    });
  }
  handleChangePreview = ({fileList }) => {
    const fileImageArray = [];
    for (let i = 0; i < fileList.length; i++) {
      let a = '';
      if (fileList[i].response) {
        a = fileList[i].response.data.source;
      } else {
        a = fileList[i].url
      }
      fileImageArray[i] = a;//a 图片url地址
    }
    setTimeout(() => {
      this.setState({
        fileImage: fileImageArray
      });
    }, 4000);
    this.setState({
      fileList
    });
  }
  beforeUpload = (file, fileList) => {
    const isLt2M = file.size / 1024 / 1024 <2;
    if (!isLt2M) {
      message.error('请重新上传小于2mb的图片!');
      return false;
    }else{
    return true;
    }
    }

  handleCancel =async () => {
    // 关闭并初始化
    this.props.form.resetFields();
    await this.setState( { 
      modalVisible: false,
      EditModalVisible: false,
      EditModalData: {},
      fileList: [],
      fileList1: [],
      fileImage: [],
      fileImage1:[],
      previewImage:[],
      describe:''
    });
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
    }

   // 提交
   handleSubmit = (e) => {
     var waresname, model, productno, price, mallprice, unit, describe, series, brand, category2;
     this.setState({ loading: true });
     waresname = document.getElementById('waresname').value;
     model = document.getElementById('model').value;
     productno = document.getElementById('productno').value;
     price = document.getElementById('price').value;
     mallprice = document.getElementById('mallprice').value;
     unit = document.getElementById('unit').value;
     series = document.getElementById('series').value;
     brand = document.getElementById('brand').value;
     category2 = this.state.category2
     this.state.waresname = waresname;
     this.state.model = model;
     this.state.productno = productno;
     this.state.price = price;
     this.state.mallprice = mallprice;
     this.state.unit = unit;
     this.state.describe = describe||"";
     this.state.series = series;
     this.state.brand = brand;
     e.preventDefault();
     this.props.form.validateFields((err, values) => {
      if (!err) {
        if(this.state.fileImage1.length==0||this.state.fileImage.length==0){
          message.info("请点击检查图片是否上传完成");
          return;
        }else if(this.state.fileImage1[0]==""||this.state.fileImage1[0]=="h"){
          message.info("正在上传，请稍后。。。。");
          return;
        }
     const data = {
       admin: this.props.currentUser.admin,
       username: this.props.currentUser.username,
       waresname: this.state.waresname,
       model: this.state.model,
       productno: this.state.productno,
       price: this.state.price,
       mallprice: this.state.mallprice,
       unit: this.state.unit,
       describe: this.state.describe,
       series: this.state.series,
       brand: this.state.brand,
       admincode:this.state.admincode,
       category1: this.state.category1,
       category2: this.state.category2,
       category3: this.state.category3,
       image1: this.state.fileImage1[0],
       image2: this.state.fileImage[0],
       image3: this.state.fileImage[1],
       image4: this.state.fileImage[2],
       billno: this.state.EditModalData.billno, // 有值则修改
     };
     http({
       method: 'post',
       api: 'setwares',
       data: {
         ...data
       }
     }).then((result) => {
       const { status, msg, data } = result;
       if (status === '0') {
         message.info('成功');
         this.handleCancel();//点击关闭
         this.props.handleRefresh();
       } else {
         message.info(msg);
       }this.setState( { 
         submitting: false,
         modalVisible: false, });
        }).catch(() => {
          this.setState({ submitting: false });
        });
      }
      });
      }

  readradio() {
    // 方法一            
    var item = null;
    var obj = document.getElementsByName("goods");
        if (obj[i].checked) {
            item = obj[i].value;                   
        }
    alert(item);
  }
//编辑
  setFieldsValue = (props) => {
  const activeItem = props.activeItem
  this.state.fileList1[0]={
    uid: 1,
    name: 'image1',
    status: 'done',
    url:activeItem.image1,
  };
  this.state.fileImage1[0]=activeItem.image1;
  for(let i=0;i<5;i++){
    let a = ""
    switch(i){
      case 0:
        a=activeItem.image2;
        break;
      case 1:
        a = activeItem.image3;
        break;
      case 2: 
        a = activeItem.image4;
        break;
      case 3: 
        a = activeItem.image5;
        break;
      case 4: 
        a = activeItem.image6;
        break;
    }
    if(!a==""){
    this.state.fileList[i]={
      uid: i,
      name: 'image'+i,
      status: 'done',
      url:a,
    };
    this.state.fileImage[i]=a 
  }
  }
  this.props.form.setFieldsValue({
    category:[props.activeItem.category1, props.activeItem.category2, props.activeItem.category3]
  })
  this.setState({
    EditModalVisible: true,
    EditModalData:props.activeItem
  });
    const billdate = moment(activeItem.billdate);
    const revedate = moment(activeItem.revedate);
  }
  
  // 关闭之后
  handleAfterClose = () => {
    this.props.form.resetFields();
  }
  render() {
    const { EditModalData, fileList1, fileList, previewVisible,previewImage} = this.state;
    const {
      addOrEdit,
      form: { getFieldDecorator, setFieldsValue, }
    } = this.props;
    const uploadButton = (
      <div>
        <Icon type="plus" />
        <div className="ant-upload-text">添加</div>
      </div>
    );
    return <Modal title={`${addOrEdit === '0' ? '新增' : '修改'}商品进货信息`} width="40%" maskClosable={false} visible={this.props.visible} onCancel={this.handleCancel} onOk={this.handleSubmit} confirmLoading={this.state.submitting} afterClose={this.handleAfterClose}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col>
              <Form.Item label="商品小图：">
                <Upload beforeUpload={this.beforeUpload} name="file" action={Api['uploadimg']} listType="picture-card" fileList={fileList1} onPreview={this.handlePreview} onChange={this.handleChangePreview1 //点击预览
                  }>
                  {fileList1.length > 0 ? null : uploadButton}
                </Upload>
                {/*1 预览是否可见 2.点击取消改变*/}
                <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                  <img alt="example" style={{ width: '100%' }} src={previewImage} />
                </Modal>
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="名称">
                {getFieldDecorator('waresname', {
                  rules: [{ required: true, message: '请输入商品名称' }],
                  initialValue: EditModalData.waresname,
                })(<Input placeholder="请输入商品名称" id="waresname" name="waresname" type="text" />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="型号">
                {getFieldDecorator('model', {
                  rules: [{ required: true, message: '请输入商品型号' }],
                  initialValue: EditModalData.model,
                })(<Input placeholder="请输入商品型号" id="model" name="model" type="text" />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="编码">
                {getFieldDecorator('productno', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请输入商品编码' }],
                  initialValue: EditModalData.productno,
                })(// <DatePicker style={{ width: '100%' }} />
                  <Input placeholder="请输入商品编码" id="productno" name="productno" type="text" />)}
              </Form.Item>
            </Col>

            <Col>
              <Form.Item label="系列">
                {getFieldDecorator('series', {
                  rules: [{ message: '请输入商品系列' }],
                  initialValue: EditModalData.series,
                })(<Input placeholder="请输入商品系列" id="series" name="series" type="text" />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="单价（元）">
                {getFieldDecorator('price', {
                  rules: [{ required: true, message: '请输入商品单价' }],
                  initialValue: EditModalData.price,
                })(<Input placeholder="请输入商品单价" id="price" name="price" type="text" />)}
              </Form.Item>
            </Col>

            <Col>
              <Form.Item label="商城单价（元）">
                {getFieldDecorator('mallprice', {
                  rules: [{ required: true, message: '请输入商品商城单价' }],
                  initialValue: EditModalData.mallprice,
                })(<Input placeholder="请输入商品商城单价" id="mallprice" name="mallprice" type="text" />)}
              </Form.Item>
            </Col>

            <Col>
              <Form.Item label="单位">
                {getFieldDecorator('unit', {
                  rules: [{ message: '请输入商品单位' }],
                  initialValue: EditModalData.unit,
                })(<Input placeholder="请输入商品单位" id="unit" name="unit" type="text" />)}
              </Form.Item>
            </Col>

            <Col>
              <Form.Item label="商城上架">
                {getFieldDecorator('checkedIndex', {
                  rules: [{ required: true, message: '请选择商城在售' }],
                  initialValue: EditModalData.checkedIndex,
                })(<Radio.Group>
                    <Radio value={0}>下架不在售</Radio>
                    <Radio value={1}>上架在售</Radio>
                  </Radio.Group>)}
              </Form.Item>
            </Col>

            <Col>
              <Form.Item label="品牌">
                {getFieldDecorator('brand', {
                  rules: [{ message: '请输入品牌' }],
                  initialValue: EditModalData.brand,
                })(<Input placeholder="请输入商品品牌" id="brand" name="brand" type="text" />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="分类">
                {getFieldDecorator('category', {
                  rules: [{ required: true, message: '请选择分类' }],
                })(
                <Cascader
                 options={this.state.options}
                 loadData={this.loadData} 
                 onChange={this.onChange}
                 displayRender={this.displayRender}
                 changeOnSelect
              />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="详情图片">
                <Upload beforeUpload={this.beforeUpload} name="file" action={Api['uploadimg']} listType="picture-card" fileList={fileList} onPreview={this.handlePreview} onChange={this.handleChangePreview}>
                  {fileList.length >= 3 ? null : uploadButton}
                </Upload>
                {/* 预览效果 */}
                <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                  <img alt="example" style={{ width: '100%' }} src={previewImage} />
                </Modal>
              </Form.Item>
            </Col>
          </Row>
        </Form>
        {/* 选择客户(不知道是否有用) */}
        <SelectCustomer customerType="0" visible={this.state.showSelectCustomer} handleVisible={bool => this.setState(
              { showSelectCustomer: bool }
            )} handleOk={item => {
            setFieldsValue({ custno: item.billno, custname: item.title });
          }} />
      </Modal>;
  }
}

CrudeAdd.propTypes = {
  addOrEdit: PropTypes.oneOf(['0', '1']) // 0新增 1编辑
};

CrudeAdd.defaultProps = {
  addOrEdit: '0'
};


export default CrudeAdd;