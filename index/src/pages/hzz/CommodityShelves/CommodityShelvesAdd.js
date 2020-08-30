import React, { PureComponent } from 'react';
import { connect } from 'dva';
import {  Icon,Modal, Select, message, Input, Row, Col, DatePicker, Form, Upload } from 'antd';
import http from '@/utils/http';
import Api from '@/common/api';

function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CommodityShelvesAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,//提交动画
      getLoading: false,//加载动画
      delLoading: false,//删除中
      EditModalData: {},//编辑区的值
      admincode:props.currentUser.admincode,
      NewEditModalData:{
      },//新增的值
      fileList:[],//存放详情图片
      previewVisible: false,//预览可见
      previewImage: '',//base64地址
      fileImage: [],//最终地址
      admin: props.currentUser.admin,
      username: props.currentUser.username, // 操作员
      waresname: '', // 名称
      model: '', // 型号
      productno: '', // 编码
      price: '', //价格
      mallprice: '', // 商城的单价
      unit: '', // 单位
      describe: '', // 描述
      billno: '', // 有值表示编辑
      checkedIndex: '',
      series: '', // 分类
      brand: '', // 品牌
      category: '',
      date:"",//生产日期
      image1:'',//头像地址
      onsale:'1'
    };
  }
  //接受父类传递的参数
  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      this.setFieldsValue(nextProps);
    }
  }
  //初始化表单
  setFormData = (newState) => {
    this.setState(state => ({
      formData: {
        ...state.formData,
        ...newState
      }
    }));
  }
  //赋值给表单
  setFieldsValue = (props) => {
    const activeItem = props.activeItem
    this.state.admin=activeItem.admin
    this.state.image1=activeItem.image1
    this.props.form.setFieldsValue({ 
      waresname:activeItem.waresname,
      billno:activeItem.billno,
      model:activeItem.model,
      productno:activeItem.productno,
      price:activeItem.price,
      waretype:"请选择商品类型",
      unit:activeItem.unit,
      place:activeItem.place,
      warranty:activeItem.warranty,
      buylimit:0,
      onsale:'上架',
      ticket:"请选择是否可用卷",
      envelope:"是否可用红包",
      description:activeItem.description,
  });
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
      this.setState({
        EditModalData:props.activeItem
      });
  }
//取消触发
handleCancel=()=>{
  this.props.form.resetFields();
   this.setState( { 
    EditModalVisible: false,
    EditModalData: {},
    fileList: [],
    fileList1: [],
    fileImage: [],
    fileImage1:[],
    previewImage:[],
    image1:''
  });
  const { handleVisible = () => null } = this.props;
  handleVisible(false);
}
//确定触发
handleSubmit =(e)=>{
  var image1,waresname,admincode,billno,model,productno,price,unit,
  waretype,place,makedate,warranty,buylimit,envelope,ticket,net,describe,onsale
  this.setState({ loading: true });
  image1 = this.state.image1
  waresname = document.getElementById("waresname").value;
  billno = document.getElementById("billno").value;
  model = document.getElementById("model").value
  productno = document.getElementById("productno").value;
  price = document.getElementById("price").value
  unit = document.getElementById("unit").value
  waretype = this.state.waretype
  place = document.getElementById("place").value
  warranty = document.getElementById("warranty").value
  buylimit = document.getElementById("buylimit").value
  envelope = this.state.envelope
  ticket = this.state.ticket
  onsale = this.state.onsale==1?true:false
  net = document.getElementById("net").value
  describe = document.getElementById("description").value
  admincode = this.state.admincode
  e.preventDefault();
  this.props.form.validateFields((err, values) => {
    if (!net) {
      message.info("请确定是否填写净含量");
      return;
    }
    if (!warranty) {
      message.info("请确定是否填写质保期");
      return;
    }
    if (!place) {
      message.info("请确定是否填写产地");
      return;
    }
    // if(net==""||warranty==""||place==""){
    //   message.info("请确定信息是否填写完成");
    //   return;
    // }else 
    if(!err) {
      const data = {
        image1:image1,
        image2:this.state.fileImage[0]?this.state.fileImage[0]:null,
        image3:this.state.fileImage[1]?this.state.fileImage[1]:null,
        image4:this.state.fileImage[2]?this.state.fileImage[2]:null,
        image5:this.state.fileImage[3]?this.state.fileImage[3]:null,
        image6:this.state.fileImage[4]?this.state.fileImage[4]:null,
        waresname:waresname,
        wareno:billno,
        model:model,
        productno:productno,
        price:price,
        unit:unit,
        waretype:waretype,
        place:place,
        makedate:this.state.makedate,
        warranty:warranty,
        buylimit:buylimit,
        envelope:envelope,
        ticket:ticket,
        net:net,
        checkedIndex:onsale,
        describe:describe,
        admin:this.state.admin,
        admincode:this.state.admincode,
        username:this.state.username
      }
     http({
       method: 'post',
       api: 'setmallwares',
       data: {
         ...data
       }
     }).then((result) => {
       const { status, msg, data } = result;
       if (status === '0') {
         message.info('添加成功 ');
         this.handleCancel();//点击关闭
         this.props.handleRefresh();
         this.props.form.resetFields();
         const { Tabvisible = () => null } = this.props;
         Tabvisible(true);
       } else {
         message.info(msg);
       }this.setState( { 
         submitting: false,
         modalVisible: false, });
        }).catch(() => {
          message.info("错误");
          this.setState({ submitting: false });
        });
      }
      });
}
//选择商品类型
handleChange=async(value)=> {
  await this.setState({
    waretype:value
  })
}
//选择商品上下架
handleChanges=async(value)=> {
  await this.setState({
    onsale:value
  })
}
//是否可用红包
handleChange1=async(value)=>{
  await this.setState({
    envelope:value
  })
}
//是否可用卷
handleChange2=async(value)=>{
  await this.setState({
    ticket:value
  })
}
//选择生产日期
onChange_makedate=async(date, dateString)=>{
  await this.setState({
    makedate: dateString
  })
}
//完全关闭后触发
handleAfterClose=()=>{
  this.props.form.resetFields();
}
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
// 上传图片
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
}
//渲染
render() {
  const {EditModalData,previewImage,fileList, previewVisible} = this.state;
  const {
    form: { getFieldDecorator, setFieldsValue}
  } = this.props;
  const uploadButton = (
    <div>
      <Icon type="plus" />
      <div className="ant-upload-text">添加</div>
    </div>
  );
    return (
      <div>
 <Modal
        title={`上架商品`}
        width="40%"
        maskClosable={false}//点击非对话框处是否可以关闭
        visible={this.props.visible}//是否显示
        onCancel={this.handleCancel}//取消
        onOk={this.handleSubmit}//确定触发
        confirmLoading={this.state.submitting}
        afterClose={this.handleAfterClose}//完全关闭后回调
      >
        <Form layout="vertical">
          <Row gutter={24}>
            <Col>
                <Form.Item label="名称">
                  {getFieldDecorator('waresname', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                    initialValue: EditModalData.waresname
                  })(
                    <Input readonly="readonly" placeholder="请输入商品名称" id="waresname" name="waresname" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="编码">
                  {getFieldDecorator('billno', {
                    rules: [{ required: true, message: '商品编码' }],
                    initialValue: EditModalData.billno
                  })(
                    <Input  readonly="readonly" placeholder="请输入商品编码" id="billno" name="billno" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="型号">
                  {getFieldDecorator('model', {
                    rules: [{ required: true, message: '请输入型号' }],
                    initialValue: EditModalData.model
                  })(
                    <Input  readonly="readonly" placeholder="请输入商品名称" id="model" name="model" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="编号">
                  {getFieldDecorator('productno', {
                    rules: [{ required: true, message: '请输入编号' }],
                    initialValue: EditModalData.productno
                  })(
                    <Input placeholder="请输入商品编号" id="productno" name="productno" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="单价">
                  {getFieldDecorator('price', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                    initialValue: EditModalData.price
                  })(
                    <Input placeholder="请输入商品名称" id="price" name="price" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="单位">
                  {getFieldDecorator('unit', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                    initialValue: EditModalData.unit
                  })(
                    <Input  readonly="readonly" placeholder="请输入商品名称" id="unit" name="unit" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品类型">
                  {getFieldDecorator('waretype', {
                    rules: [{ required: true, message: '请输入商品类型' }],
                    initialValue:'请选择商品类型'
                  })(
                      <Select
                      onChange={this.handleChange}
                      >
                        <Select.Option value="0">普通商品</Select.Option>
                        <Select.Option value="1">优惠商品</Select.Option>
                        <Select.Option value="2">推荐商品</Select.Option>
                        <Select.Option value="3">新品</Select.Option>
                        <Select.Option value="4">限时优惠</Select.Option>
                        <Select.Option value="5">限量请购</Select.Option>
                        <Select.Option value="6">热卖</Select.Option>
                        <Select.Option value="7">打折促销</Select.Option>
                      </Select>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="选择上下架">
                  {getFieldDecorator('onsale', {
                    rules: [{ required: true, message: '请输入商品类型' }],
                    initialValue:'请选择商品类型'
                  })(
                      <Select
                      onChange={this.handleChanges}
                      >
                        <Select.Option value="0">下架</Select.Option>
                        <Select.Option value="1">上架</Select.Option>
                      </Select>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="产地">
                  {getFieldDecorator('place', {
                    rules: [{ required: true, message: '请输入商品产地' }],
                    initialValue: EditModalData.place
                  })(
                    <Input placeholder="请输入商品产地" id="place" name="place" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="生产日期">
                  {getFieldDecorator('makedate')(
                    <Input hidden="hidden"/>
                  )
                  }
                  {getFieldDecorator('datat')(
                    <DatePicker
                    style={{ width: '100%' }}
                    onChange = {this.onChange_makedate}
                  />
                  )
                  }
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="质保期">
                  {getFieldDecorator('warranty', {
                    rules: [{ required: true, message: '请输入质保期' }],
                    initialValue: EditModalData.warranty
                  })(
                    <Input placeholder="请输入质保期" id="warranty" name="warranty" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="限购">
                  {getFieldDecorator('buylimit', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                  })(
                    <Input placeholder="请输入商品名称" id="buylimit" name="buylimit" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="是否可用红包">
                  {getFieldDecorator('envelope', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                  })(
                    <Select
                    onChange={this.handleChange1}
                    >
                      <Select.Option value="1">是</Select.Option>
                      <Select.Option value="0">否</Select.Option>
                    </Select>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="是否可用卷">
                  {getFieldDecorator('ticket', {
                    rules: [{ required: true, message: '是否可用卷' }],
                  })(
                    <Select
                    onChange={this.handleChange2}
                    >
                      <Select.Option value="1">是</Select.Option>
                      <Select.Option value="0">否</Select.Option>
                    </Select>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="净含量">
                  {getFieldDecorator('net', {
                    rules: [{ required: true, message: '请输入净含量' }],
                  })(
                    <Input placeholder="请输入净含量" id="net" name="net" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="商品描述">
                  {getFieldDecorator('description', {
                    rules: [{ required: true, message: '请输入商品描述' }],
                  })(
                    <Input placeholder="请输入商品描述" id="description" name="description" type="text"/>
                  )}
                </Form.Item>
              </Col>
              <Col>
              <Form.Item label="详情图片">
              <Upload 
                  name = "file"
                  action={Api['uploadimg']}
                  listType="picture-card"
                  fileList={fileList}
                  onPreview={this.handlePreview}
                  onChange={this.handleChangePreview}
                >
                {fileList.length >= 3 ? null : uploadButton}
                </Upload>
                {/* 预览效果 */}
                <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                  <img  alt="example" style={{ width: '100%' }} src={previewImage} />
                </Modal> 
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </Modal>
      </div>
    );
  }
}

export default CommodityShelvesAdd;