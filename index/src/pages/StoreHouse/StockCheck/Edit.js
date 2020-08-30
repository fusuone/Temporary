import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, Form, Input, Row, Col, DatePicker, Upload } from 'antd';
import { connect } from 'dva';
import moment from 'moment';
import http from '@/utils/http';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const { Option } = Select;
import Api from '@/common/api';
import { set } from 'zrender/lib/core/vector';
//盘点页面

@connect(({
    user
  }) => ({
    currentUser: user.currentUser
  }))
  @Form.create()
class Edit extends PureComponent{
  constructor(props){
    super(props);
    this.state={
      getLoading: true,
      loading: false,
      visible: false,
      optionWaresData: [],  //商品名称选项值
      optionData: "", //已选择的商品名称数据
      defaultEditData: {},  //默认编辑数据
      modalTitle: "添加",
      submitData: {},
      selectDate: "",
    }
  }

  componentDidMount(){
    this.props.onRef(this);
    this.getwaresData();
  }

  // 获取商品列表
  getwaresData = () => {
    http({
        method: 'post',
        api: 'getwares',
        params: {
          admin: this.props.currentUser.admin
        }
      }).then((result) => {
        const { status, msg, data } = result;
        console.log(data.list)
        if (status === '0') {
          this.setState({
            optionWaresData: data.list
          });
        } else {
          this.setState({
            optionWaresData: []
          });
        }
      }).catch(() => {
        // this.setState({ getLoading: false });
      });
    }

    componentWillReceiveProps(){

    }
    
    showModal = () => {
      this.setState({
        visible: true,
      });
    };
    
    handleOk = () => {
      this.setState({ loading: true });
      this.handleSubmit();
      setTimeout(() => {
        this.setState({ loading: false, });
      }, 3000);
    };
  
    handleCancel = () => {
      this.props.form.resetFields();
      this.setState({ 
        visible: false,
        optionData: ""
        });
    };

    //表单
    handleSubmit = e => {
      let { optionData, defaultEditData} = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
        if (!err) {
          if(this.state.defaultEditData.checkdate=="" && this.state.selectDate=="" ){
            message.warn("请选择盘点时间");
            return;
          }
        const  params1= {
          warepic: !values.waresname?defaultEditData.warepic:values.waresname.image1,
          warename: !values.waresname?defaultEditData.warename:values.waresname.waresname,
          unit: !values.waresname?defaultEditData.unit:values.waresname.unit,
          billno: defaultEditData.billno,
          admin: this.props.currentUser.admin,
          username: this.props.currentUser.username,
          userno: this.props.currentUser.userno,       
          wareno: values.wareno,
          productno: values.productno,
          checkdate: values.checkdate,
          qty: values.qty,
          qty1: values.qty1,
          qty2: values.qty2,
          qty3: values.qty3,
          newqty: values.newqty,
          newqty1: values.newqty1,
          newqty2: values.newqty2,
          newqty3: values.newqty3,
          remark: values.remark
        };
        http({
          method: 'post',
          api: 'check_wares',
          data: params1,
        }).then((result) => {
          const { status, msg, data } = result;
          if (status === '0') {
            this.props.form.resetFields();
            setTimeout(() => {
              this.setState({
                optionData: "",
                visible: false,
              });
            }, 8000);
          } else {
            message.info("修改失败");
          }
        }).catch(() => {
          message.info("修改失败");
        });
      }
    });
  };
    
  handleSelectChange = value => {
    this.setState({
      optionData:value
    });
  };
    
  getOption = () =>{
    let opdata = this.state.optionWaresData;
    let a;
    opdata.map((value,key)=>{
      a = <Option value={key}>{value.waresname}</Option>
    })
    return a;
  }

  // 盘点日期选择
  oneDatePickerChange = (date, dateString) => {
    this.setState({
      selectDate: dateString
    });
  }

  edit = (e) => {
    let { modalTitle, EditModalVisible, data } = e;
    this.setState({
      modalTitle: modalTitle, 
      visible: EditModalVisible,
      defaultEditData: data
    });
  }

  render() {
    const { modalTitle, visible, loading, defaultEditData, selectDate } = this.state;
    // const { data } = this.props.data;
    const { getFieldDecorator } = this.props.form;
    return (
      <div>
        <Modal
          visible={visible}
          maskClosable={false}
          title= {!modalTitle?"添加":modalTitle}
          onOk={this.handleOk}
          onCancel={this.handleCancel}
          footer={[
          <Button key="back" onClick={this.handleCancel}>
              取消
          </Button>,
          <Button key="submit" type="primary" loading={loading} onClick={this.handleSubmit}>
              确认
          </Button>,
          ]}
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="商品名称：">
                    {getFieldDecorator('waresname', {
                    rules: [{ required: true, message: '请选择商品名称' }],
                    initialValue: this.state.optionData!=""?this.state.optionData.waresname:defaultEditData.warename
                    })(
                      <Select
                          placeholder="请选择商品名称"
                          onChange={this.handleSelectChange}
                        >
                          { !this.state.optionWaresData ? "" : this.state.optionWaresData.map((value,index) =>
                          <Option key={index} value={value}>{value.waresname}</Option>)
                          
                          }
                        </Select>
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品编号：">
                    {getFieldDecorator('wareno', {
                    rules: [{ required: true, message: '请输入商品编号' }],
                    initialValue: defaultEditData.wareno
                    })(
                    <Input placeholder="请输入商品名称" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品二维码：">
                    {getFieldDecorator('productno', {
                    rules: [{ required: true, message: '请输入商品二维码' }],
                    initialValue: defaultEditData.productno
                    })(
                    <Input placeholder="请输入商品二维码" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品原数量：">
                    {getFieldDecorator('qty', {
                    initialValue: defaultEditData.qty?defaultEditData.qty:"0"
                    })(
                    <Input disabled="true" placeholder="请输入商品原数量" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品原次品：">
                    {getFieldDecorator('qty1', {
                    initialValue: defaultEditData.qty1?defaultEditData.qty1:"0"
                    })(
                    <Input disabled="true" placeholder="请输入商品原次品" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品原坏品：">
                    {getFieldDecorator('qty2', {
                    // rules: [{ required: true, message: '请输入商品原坏品' }],
                    initialValue: defaultEditData.qty2?defaultEditData.qty2:"0"
                    })(
                    <Input disabled="true" placeholder="请输入商品原坏品" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品原其它：">
                    {getFieldDecorator('qty3', {
                    // rules: [{ required: true, message: '请输入商品原其它' }],
                    initialValue: defaultEditData.qty3?defaultEditData.qty3:"0"
                    })(
                    <Input disabled="true" placeholder="请输入商品原其它" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品现数量：">
                    {getFieldDecorator('newqty', {
                    rules: [{ required: true, message: '请输入商品现数量' }],
                    initialValue: defaultEditData.newqty
                    })(
                    <Input placeholder="请输入商品现数量" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品现次品：">
                    {getFieldDecorator('newqty1', {
                    rules: [{ required: true, message: '请输入商品现次品' }],
                    initialValue: defaultEditData.newqty1
                    })(
                    <Input placeholder="请输入商品现次品" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品现坏品：">
                    {getFieldDecorator('newqty2', {
                    rules: [{ required: true, message: '请输入商品现坏品' }],
                    initialValue: defaultEditData.newqty2
                    })(
                    <Input placeholder="请输入商品现坏品" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="商品现其它：">
                    {getFieldDecorator('newqty3', {
                    rules: [{ required: true, message: '请输入商品现其它' }],
                    initialValue: defaultEditData.newqty3
                    })(
                    <Input placeholder="请输入商品现其它" />
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="盘点日期：">
                    {getFieldDecorator('checkdate', {
                    rules: [{ required: true, message: '请选择盘点日期' }],
                    initialValue: selectDate!=""?selectDate:defaultEditData.checkdate
                    })(
                      <div>
                          <Input hidden="hidden" value={selectDate!=""?selectDate:defaultEditData.checkdate} />
                          <DatePicker
                            style={{width:"100%",float:"left"}}
                            onChange={this.oneDatePickerChange} 
                            placeholder="请选择盘点日期"
                            defaultValue={moment(defaultEditData.checkdate)}
                          />
                      </div> 
                    )}
                  </Form.Item>
                </Col>
                <Col>
                  <Form.Item label="备注：">
                    {getFieldDecorator('remark', {
                    rules: [{ required: true, message: '请输入备注信息' }],
                    initialValue: defaultEditData.remark
                    })(
                    <textarea style={{width:"100%",height:"100px"}} placeholder="请输入备注信息" />
                    )}
                  </Form.Item>
                </Col>
            </Row>
          </Form>
        </Modal>
      </div>
    );
  }
}

export default Edit;