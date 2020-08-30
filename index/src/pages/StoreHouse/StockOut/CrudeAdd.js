import React, { Fragment,PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Row, Col, Button,Modal, message, Form, Select,Table,Icon ,InputNumber } from 'antd';
import moment from 'moment';
import http from '@/utils/http';

const { Option } = Select;

@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class CrudeAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      getLoading: false,
      billno:'',
      showSelectGecarplate: false,
      carData: [],
      contractData:[],
      moudelDate:[],
      detailDate:false,
      EditData:{},
      qty:''
    };
    this.columns=[
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        render: (text, record) => {
          return <div><img style={{ width:100,height:100}} src={record.image1} /></div>;
        },
      },{
        title: '名称',
        dataIndex: 'waresname',
        width: "13%"
      },{
        title: '单价',
        dataIndex: 'price',
        width: "10%"
      },{
        title: '数量',
        dataIndex: 'qty',
        width: "10%"
      },{
        title: '型号',
        dataIndex: 'model',
        width: "10%"
      },{
        title: '单位',
        dataIndex: 'unit',
        width: "10%"
      },{
        title: '是否可用红包',
        dataIndex: 'model',
        width: "10%",
        render: (text, record) => {
          return record.envelope==0?'否':'是';
        },
      },{
        title: '是否可用卷',
        dataIndex: 'ticket',
        width: "10%",
        render: (text, record) => {
          return record.envelope==0?'否':'是';
        },
      },
      {/*占位使其不会变型*/}, 
      {
        title: '操作',
        fixed: 'right',
        width: 100,
        render: text => (
          <div>
            <a onClick={() => this.gotoEdit(text)}><Icon type="plus-circle" style={{fontSize:'40px'}}/></a>
          </div>
        )
      }
    ]
  }
  //获取批发商信息
  getList = () => {
    const { getLoading } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getcustomer',
      params: {
        admin: this.props.currentUser.admin,
        uid: this.props.currentUser.userno,
        ispaging:2,
        custname: '',
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            carData: data.list,
            getLoading: false,
          });
          // console.log(data);
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
  getcontractList=()=>{
      const { billno} = this.state;
      this.setState({ getLoading: true });
      http({
        method: 'get',
        api: 'get_contract',
        params: {
          customerno:billno
        },
      })
        .then(result => {
          const { status, msg, data } = result;
          console.log(data)
          if (status === '0') {
            this.setState({
              contractData: data.list,
              getLoading: false,
            });
          } else {
            message.warn(msg);
            this.setState({
              getLoading: false,
            });
          }
        })
        .catch(() => {
          this.setState({ getLoading: false });
        });
  }
  getmallwares=()=>{
    const { billno} = this.state;
    if(!billno==''){
      this.setState({ getLoading: true });
      http({
        method: 'get',
        api: 'getmallwares',
        params: {
          admin: this.props.currentUser.admin,
          keyword: ''
        },
      })
        .then(result => {
          const { status, msg, data } = result;
          console.log(data)
          if (status === '0') {
            this.setState({
              moudelDate: data.list,
              showSelectGecarplate:true,
              getLoading: false,
            });
          } else {
            message.warn(msg);
            this.setState({
              getLoading: false,
            });
          }
        })
        .catch(() => {
          this.setState({ getLoading: false });
        });
      }else{
        alert('请选择要出货的商家')
      }
  }
  handleCancel = () => {
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
  };
  handleCancel1 = () => {
   this.setState({
    showSelectGecarplate:false
   })
   const { handleVisible = () => null } = this.props;
    handleVisible(false);
  };
  handleCancel2 = () => {
    this.setState({
      detailDate:false
    })
   };
  //提交
  referok=(e)=>{
    this.setState({ loading: true });
    const{billno,customername,contractno,EditData,qty}=this.state
    if(qty>EditData.qty){
      alert("选择小于库存的数量")
    }else{
      EditData.qty=qty
    const data = {
      admin: this.props.currentUser.admin,
      userno: this.props.currentUser.userno, // 操作员
      username: this.props.currentUser.username, // 操作员
      customerno:billno,// 供应商编号
      customername:customername, // 供应商名称
      contractno:contractno?contractno:'', // 合同的编号
      flag:1,// 0进1出
      goodsList:[{
        wareno:EditData.wareno,
        warename:EditData.waresname2,
        image1:EditData.image1,
        image2:EditData.image2,
        image3:EditData.image3,
        price:EditData.price,
        unit:EditData.unit,
        model:EditData.model,
        productno:EditData.productno,
        serialno:EditData.series,
        description:EditData.description,
        qty:EditData.qty,
        qty1:'',
        qty2:'',
        qty3:''
      },]
    };
    console.log(data)
    http({
      method: 'post',
      api: 'setstock',
      data: {
        ...data
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        message.info('成功');
      } else {
        message.info(msg);
      }this.setState( { 
        submitting: false,
        detailDate: false,
       });
       this.getmallwares()
       }).catch(() => {
         this.setState({ submitting: false });
       });
    }
  }
  //完全关闭之后
  handleAfterClose = () => {
    this.props.form.resetFields();
  };
  onFocus=()=>{
    this.getList()
  }
  getdd=(input, option)=>{
     return option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
  }
  onChange =async(value,input) => {
    await this.setState({
      billno:input.props.value,
      customername:input.props.children
    })
    this.getList()
    this.getcontractList()
    this.props.form.setFieldsValue({
      warwss:''
    })
  };
  onChange1 =async(value,input) => {
    await this.setState({
      contractno:input.props.value
    })
    console.log(input)
    this.getList()
    this.getcontractList()
  };
  onChange2 =async(value) => {
      this.setState({
        qty:value
      })
  }
  //点击加号进货
  gotoEdit=async(text)=>{
     await this.setState({
        detailDate:true,
        EditData:text
      })
      console.log(text)
  }
  render() {
    const {
      form: { getFieldDecorator, disabled, multiple },
    } = this.props;
    const { submitting,showSelectGecarplate,moudelDate,detailDate,EditData} = this.state;
    return (
      <Modal
        title={`选择出货商品`}
        width="40%"
        maskClosable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleCancel}
        confirmLoading={submitting}
        afterClose={this.handleAfterClose}
      >
        <Form layout="vertical">
          <Row gutter={24}>
            <Col>
              <Form.Item label="商品类型">
                {getFieldDecorator('waretype', {
                  rules: [{ required: true, message: '请输入商品类型' }],
                })(
                  <Select
                    showSearch
                    onFocus={this.onFocus}
                    onSearch={this.onFocus}
                    placeholder='请选择供应商'
                    onChange={this.onChange}
                    filterOption={this.getdd}
                  >
                    {this.state.carData.map(function(value) {
                      return (
                        <Option key={value.id} value={value.billno}>
                          {value.title}
                        </Option>
                      );
                    })}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="选择合同">
                {getFieldDecorator('warwss')
                (
                  <Select
                    showSearch
                    placeholder='请选择供应商'
                    onChange={this.onChange1}
                    filterOption={(input, option) =>
                      option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
                    }
                  >
                    {this.state.contractData.map(function(value) {
                      return (
                        <Option key={value.id} value={value.contrano}>
                          {value.title}
                        </Option>
                      );
                    })}
                  </Select>
                )}
              </Form.Item>
            </Col>
          </Row>
        </Form>
        <Button type="primary"
        onClick={this.getmallwares}
        >选择商品</Button>
        <Modal
        visible={showSelectGecarplate}
        title={`选择出货商品`}
        width="60%"
        maskClosable={false}
        onCancel={this.handleCancel1}
        confirmLoading={submitting}
        footer={[
          <Button type="primary" key="back" onClick={this.handleCancel1}>关闭</Button>
        ]}
        afterClose={this.handleAfterClose}
        >
        <Table
          rowKey={record=>record.id}
          scroll={{ x: 1300, y: 600 }} //高
          dataSource={moudelDate} //数据来源
          columns={this.columns} //每行显示
          pagination={{
            pageSize: 15,
            defaultCurrent: 1,            
          }}
        />
          <Modal
          visible={detailDate}
          title={`${EditData.waresname}`}
          width="30%"
          destroyOnClose
          maskClosable={false}
          onCancel={this.handleCancel2}
          confirmLoading={submitting}
          footer={[
            <Button type="primary" key="back" onClick={this.referok}>确定</Button>
          ]}
          afterClose={this.handleAfterClose}
          >
           <p>库存数量:<span style={{marginLeft:'1vw',color:'red'}}>{EditData.qty}</span></p>
          <span style={{marginRight:'1vw'}}>出货数量:</span>  
            <InputNumber
               style={{ width:"20vw"}}
               defaultValue={1}
               min={1}
              //  max={EditData.qty}
               onChange={this.onChange2}
            />
          </Modal>
        </Modal>
      </Modal>
      
    );
  }
}
CrudeAdd.propTypes = {
  addOrEdit: PropTypes.oneOf(['0', '1']), // 0新增 1编辑
};

CrudeAdd.defaultProps = {
  addOrEdit: '0',
};

export default CrudeAdd;
