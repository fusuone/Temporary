import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker, Input, Form, Row, Col, message } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './Receipt.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;
const { Search } = Input;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class Receipt extends PureComponent{
    constructor(props){
        super(props);
        this.state={
          getLoading: false,
          RealNameDate : [],
          RealName_billno: "",
          modalVisible: false, //modal
          status: -10,
          modalData: {}, 
          ////////////////////
          detailModalVisible: false, //详情页面显示状态
          detailModalData: {},
          total: '',
          reqParams: {
            page: 1,
            pagesize: 15,
            begin: '',
            end: '',
            searchkey: '',
          },
          modifyModalData: {},
         
        }
        this.columns = [
            {
              title: '税号',
              width: 180,
              dataIndex: 'dutyno',
              key: 'dutyno',  
              fixed: 'left',
            },
            {
              title: '发票时间',
              dataIndex: 'billdate',
              key: 'billdate',
              width: 130,
              render: (text, record) => {
                let date = record.billdate.substr(0,10);
                return date;

              }
            },
            {
                title: '发票类型',
                dataIndex: 'receipt_type',
                key: 'receipt_type',
                width: 100,
            },
            {
              title: '发票是否已开',
              dataIndex: 'fill',
              key: 'fill',
              width: 130,
              render: (text,record) => {
                let fill = "";
                if(record.fill==0){
                  fill = "发票未开";
                }else if(record.fill==1){
                  fill = "发票已开";
                }
                return fill;
              }
            },
            {
              title: '注册公司',
              width: 280,
              dataIndex: 'company',
              key: 'company',
            },
            {
              title: '注册电话',
              dataIndex: 'register_tel',
              key: 'register_tel',
              width: 170,
            },
            {
              title: '注册地址',
              dataIndex: 'register_addr', 
              key: 'register_addr',
              width: 270,
            },
            {
              title: '开户银行',
              dataIndex: 'bank',
              key: 'bank',
              width: 250,
            },
            {
              title: '开户银行号',
              dataIndex: 'bankno',
              key: 'bankno',
              width: 200,
            },
            {
              title: '收票人',
              dataIndex: 'addressee',
              key: 'addressee',
              width: 200,
            },
            {
              title: '收票人地址',
              dataIndex: 'addressee_addr',
              key: 'addressee_addr',
              width: 300,
            },
            {
              title: '收票人电话',
              dataIndex: 'addressee_tel',
              key: 'addressee_tel',
            },
            // {
            //   title: '编号',
            //   dataIndex: 'billno',
            //   key: 'billno',
            // },
            {
              title: '操作',
              key: 'operation',
              fixed: 'right',
              width: 130,
              render: (text, record) =>{
                this.setState({
                  RealName_billno: record.billno
                });
                //操作菜单
                let OperateMenu = (
                  <Menu>
                    <Menu.Item>
                      {/* <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,0)}>寄出发票</a> */}
                      <a className={styles.operrateColor} onClick={(ev) => this.showModal(ev,record,0)}>寄出发票</a>
                    </Menu.Item>
                  </Menu>
                );
                return(
                  <div>
                  <a　onClick={(ev) => {this.detailModal(ev,record)}}>详请</a>
                  <Divider type="vertical" />
                  <Dropdown overlay={OperateMenu}>
                    <span style={{color:"#1890ff"}} className="ant-dropdown-link" href="#">
                      更多 <Icon type="down" />
                    </span>
                  </Dropdown>
                </div>
                );
              }
                
            },
          ];
    }

    


    componentDidMount() {
      this.getList();
    };

    //设置modalData
    getModalData = (key) =>{
      let key1 = key - 1;
      let modalData1 = this.state.RealNameDate[key1]
      this.setState({
        modalData: modalData1
      });
    }

    //获取审核列表数据
    getList = () => {
      let { currentUser } = this.props;
      let { reqParams } = this.state;
      http({
        method: 'post',
        api: 'getinvoicelist',
        params: reqParams,
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            RealNameDate: data.list,
            getLoading: false,
            total: data.total,
          });
        } else {
          message.warn(msg);
          this.setState({
            RealNameDate: [],
            getLoading: false
          });
        }
      }).catch(() => {
        this.setState({ getLoading: false });
      });
    }


    //modal
    showModal = (ev,record,d) => {
      this.setState({
        modalVisible: true,
        modifyModalData: record
      });
    };
  
    handleOk = e => {
      this.setState({
        status: 2
      }),this.setRealNameList();
    };
  
    //取消录入操作
    handleCancel = e => {
      this.props.form.resetFields();
      this.setState({
        modalVisible: false,
        modifyModalData: {}
      });
    };
    //未通过
    notAdopted = (e) =>{
      this.setState({
        status: -1
      }),this.setRealNameList();
    }
    //////////////


    ///未通过原因 

    onChange = (value) => {
    }

    onBlur = () => {
    }

    onFocus = () => {
    }

    onSearch = (val) => {
    }
    ///
    

    //更新审核列表数据
    setRealNameList = () => {
      if(this.state.status==-10) {return;}
      http({
        method: 'post',
        api: 'set_realname',
        params: {
          userno: this.props.currentUser.userno,
          billno: this.state.modalData["billno"],
          status: this.state.status
        }
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({ 
            getLoading: false,
            modalVisible: false,
            status: -10
          }),this.getList();
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
            status: -10
          });
        }
      }).catch(() => {
        this.setState({ getLoading: false, status: -10 });
      });
    }


    /////////////////////////////////////////////////
    //详情modal
    detailModal  =(ev,data) =>{
        this.setState({
            detailModalData: data,
            detailModalVisible: true
        });
    }

    detailHandleCancel = (e) => {
        this.setState({
            detailModalVisible: false
        });
    }

    getStatus = (d) => {
        let status = ""
        if(d == 0){
          status = "未提交";
        }else if(d == 1){
          status = "已提交(审核中)";
        }else if (d == 2){
            status = "通过";
        }else if (d == 3){
            status = "不通过";
        }else if(d == -1){
            status = "无效";
        }
        return status;
    }

    //分页
    getPaginationdata = (page, pageSize) => {
        this.state.reqParams.page=page;
        this.getList();
    }
    //日期选择
    onChangeRangePicker = (dates,dateStrings) =>　{
        this.state.reqParams.begin = dateStrings[0];
        this.state.reqParams.end = dateStrings[1];
        this.getList();
    }

    //搜索
    onSearch = (value) => {
      this.state.reqParams.searchkey = value;
      this.getList();
    }


    confirm = (ev,record,d) => {
      let calltxt = "";
      switch(d){
        case 0: 
          calltxt = "寄出发票";
          break;
      }
      Modal.confirm({
        title: `发票是否已开状态设置为 ${calltxt}`,
        content: `你确定要把税号为 ${record.dutyno} 的发票是否已开状态状态设置为 “${calltxt}” 吗？`,
        okText: '确认',
        cancelText: '取消',
        onOk: () => this.setDutynoStatus(record,calltxt),
        onCancel: this.confirmHandleCencle,
      });
    }


    //设置跟进状态
    setDutynoStatus = (record,d) => {
      let calltxt = d;
      http({
        method: 'post',
        api: 'setcalltxt',
        params: { 
          flag: this.state.reqParams.flag,
          billno: record.billno,
          calltxt: calltxt,
        },
      }).then((result) => {
        const { status, msg, data } = result;
        if (status == '0') {
          message.info('修改成功');
          this.handleCancel();
        } else {
          message.info(msg);
        }
      }).catch(() => {
        message.info('操作失败');
      });
    }


    //提交表单
    handleSubmit = e => {
      let { modifyModalData } = this.state;
       e.preventDefault();
       this.props.form.validateFields((err, values) => {
        if (!err) {
          http({
            method: 'post',
            api: 'postinvoice',
            params: { 
              billno: modifyModalData.billno,
              express_name: values.express_name,
              express_no: values.express_no,
            },
          }).then((result) => {
            const { status, msg, data } = result;
            if (status == '0') {
              message.info('修改成功');
              this.getList();
              this.handleCancel();
            } else {
              message.info(msg);
            }
          }).catch(() => {
            message.info('操作失败');
          });
        }
      });
    }





    render(){
      const { getLoading, detailModalData, modifyModalData } = this.state;
      const { Option } = Select; 
      const { getFieldDecorator } = this.props.form;  
      return(
        <PageHeaderWrapper>
          <div>
            <div  className={styles.example}>
              <Spin spinning={ getLoading } />
            </div>
            <Card>
              <RangePicker size="large" style={{margin: "0 0 15px 0"}} onChange={this.onChangeRangePicker} />
              <Divider type="vertical" />
              <Search 
                placeholder="请输入税号或公司名称" 
                onSearch={this.onSearch} 
                enterButton="搜索"
                size="large"
                style={{ width: 320, height: "20px" }}
                />
              <Table 
                  onRow={record => {
                  return {
                      onClick: event => {

                      this.setState({
                          modalData: record,
                          RealName_billno: record.billno
                      });
                      }, 
                  };
                  }}
                  columns={this.columns} 
                  dataSource={this.state.RealNameDate} 
                  scroll={{ x: 2500, y: 500 }} 
                  pagination={{
                      pageSize:this.state.reqParams.pagesize,
                      total: this.state.total,
                      onChange: this.getPaginationdata,
                  }}
            />
            </Card>
            {/* modal */}
            <div>
              <Modal
                title="发票信息录入"
                visible={this.state.modalVisible}
                onOk={this.handleOk}
                onCancel={this.handleCancel}
              //   width={1000}
                maskClosable={false}
                footer={[
                  <Button key="back" onClick={this.handleCancel}>
                    取消
                  </Button>,
                  // <Button type="danger" onClick={this.notAdopted}>
                  //   未通过
                  // </Button>,
                  <Button key="submit" type="primary" onClick={this.handleSubmit}>
                    录入
                  </Button>,
                ]}
              >
                <div className={styles.detailContainer}>
                  <h3 style={{color:"red",fontWeight:"700",width:"100%",textAlign:"center"}}>发票详情</h3>
                  <p><span>税号：</span><span className="content">{modifyModalData.dutyno}</span></p>
                  <p><span>发票是否已开：</span><span>{modifyModalData.fill==0?"未开":"已开"}</span></p>
                  <p><span>发票时间：</span><span>{modifyModalData.billdate}</span></p>
                  <p><span>发票类型：</span><span>{modifyModalData.receipt_type}</span></p>
                  <p><span>注册公司：</span><span>{modifyModalData.company}</span></p>
                  <p><span>注册电话：</span><span>{modifyModalData.register_tel}</span></p>
                  <p><span>注册地址：</span><span>{modifyModalData.register_addr}</span></p>
                  <p><span>开户银行：</span><span>{modifyModalData.bank}</span></p>
                  <p><span>开户银行号：</span><span>{modifyModalData.bankno}</span></p>
                  <p><span>收票人：</span><span>{modifyModalData.addressee}</span></p>
                  <p><span>收票人地址：</span><span>{modifyModalData.addressee_addr}</span></p>
                  <p><span>收票人电话：</span><span>{modifyModalData.addressee_tel}</span></p>
                </div>
                <div style={{width:"100%",height:"1px",background:"red",marginBottom:"20px"}}></div>
                <h3 style={{color:"red",fontWeight:"700",width:"100%",textAlign:"center"}}>添加物流信息</h3>
                <Form layout="vertical">
                  <Row gutter={24}>
                      <Col>
                      <Form.Item label="物流公司：">
                          {getFieldDecorator('express_name', {
                          rules: [{ required: true, message: '请输入物流公司' }],
                          initialValue: modifyModalData.express_name
                          })(
                          <Input placeholder="请输入物流公司" />
                          )}
                      </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="物流单号：">
                            {getFieldDecorator('express_no', {
                            rules: [{ required: true, message: '请输入物流单号' }],
                            initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入物流单号" />
                            )}
                        </Form.Item>
                      </Col>
                  </Row>
              </Form>
              </Modal>
            </div>
            {/* 详情 */}
            <Modal
                title="发票详情"
                visible={this.state.detailModalVisible}
                onCancel={this.detailHandleCancel}
              //   width={1000}
                maskClosable={false}
                footer={[
                  <Button type="primary" key="back" onClick={this.detailHandleCancel}>
                    关闭
                  </Button>,
                ]}
              >
                <div className={styles.detailContainer}>
                  <p><span>税号：</span><span className="content">{detailModalData.dutyno}</span></p>
                  <p><span>发票是否已开：</span><span className="content">{detailModalData.fill==0?"未开":"已开"}</span></p>
                  <p><span>发票时间：</span><span className="content">{detailModalData.billdate}</span></p>
                  <p><span>发票类型：</span><span className="content">{detailModalData.receipt_type}</span></p>
                  <p><span>注册公司：</span><span className="content">{detailModalData.company}</span></p>
                  <p><span>注册电话：</span><span className="content">{detailModalData.register_tel}</span></p>
                  <p><span>注册地址：</span><span className="content">{detailModalData.register_addr}</span></p>
                  <p><span>开户银行：</span><span className="content">{detailModalData.bank}</span></p>
                  <p><span>开户银行号：</span><span className="content">{detailModalData.bankno}</span></p>
                  <p><span>收票人：</span><span className="content">{detailModalData.addressee}</span></p>
                  <p><span>收票人地址：</span><span className="content">{detailModalData.addressee_addr}</span></p>
                  <p><span>收票人电话：</span><span className="content">{detailModalData.addressee_tel}</span></p>
                  <p><span>物流公司：</span><span>{modifyModalData.express_name}</span></p>
                  <p><span>物流号：</span><span>{modifyModalData.express_no}</span></p>
                </div>
              </Modal>
          </div>
        </PageHeaderWrapper>
      );
    }
}



export default Receipt;