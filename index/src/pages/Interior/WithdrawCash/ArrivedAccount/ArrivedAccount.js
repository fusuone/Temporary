import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker, Form, Row, Col, Input, message } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from '../WithdrawCash.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class ArrivedAccount extends PureComponent{
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
          examineaModalData: {},
          total: '',
          reqParams: {
            page: 1,
            pagesize: 15,
            begin: '',
            end: '',
            status: 3,
          }
         
        }
        this.columns = [
        {
          title: '申请人名称',
          width: 150,
          dataIndex: 'name',
          key: 'name',
          fixed: 'left',
        },
        { 
          title: "申请时间",
          width: 200,
          dataIndex: 'add_date',
          key: 'add_date',
        },
        {
          title: '微信ID',
          width: 280,
          dataIndex: 'wx_id',
          key: 'wx_id',  
        },
        {
            title: '手机',
            dataIndex: 'mobile',
            key: 'mobile',
            width: 180,
        },
        {
          title: '银行编号',
          dataIndex: 'mobile', 
          key: 'mobile',
          width: 200,
        },
        {
          title: '银行名称',
          dataIndex: 'bank_name',
          key: 'bank_name',
          width: 200,
        },
        {
          title: '银行卡号',
          dataIndex: 'bank_card_id',
          key: 'bank_card_id',
          width: 200,
        },
        {
          title: '持卡人',
          dataIndex: 'cardholder',
          key: 'cardholder',
          width: 150,
        },
        {
          title: '实际提现金额',
          dataIndex: 'money', 
          key: 'money',
          width: 200,
        },
        {
          title: '剩余金额',
          dataIndex: 'z_money',
          key: 'z_money',
          width: 200,
        },
        {
          title: '壹软收取的手续费',
          dataIndex: 'wx_charge',
          key: 'wx_charge',
          width: 200,
        },
        {
          title: '状态',
          dataIndex: 'status', 
          key: 'status',
          width: 100,
          render: (text,record) => {
            let t = this.getStatus1(record.status);
            return t;
          }
        },
        {
          title: '审核日期',
          dataIndex: 'check_date',
          key: 'check_date',
          width: 200,
        },
        {
          title: '微信代付成功时间',
          dataIndex: 'payment_time',
          key: 'payment_time',
          width: 200,
        },
        {
          title: '审核员ID',
          dataIndex: 'auditor_id',
          key: 'auditor_id',
          width: 200,
        },
        {
          title: '到账类型',
          dataIndex: 'type',
          key: 'type',
          width: 100,
          render: (text, record) => {
            let txt = "";
            if(record.type==1){
              txt = "银行";
            }else if(record.type==2){
              txt = "微信钱包";
            }
            return txt;
          }
        },
        {
          title: '持卡人身份',
          dataIndex: 'user_type',
          key: 'user_type',
          width: 150,
          render: (text, record) => {
            let txt = "";
            if(record.type==0){
              txt = "商家";
            }else if(record.type==1){
              txt = "业务员";
            }
            return txt;
          }
        },
        {
          title: '审核原因',
          dataIndex: 'reason',
          key: 'reason',
          width: 350,
        },
        {
          title: '微信代付失败原因',
          dataIndex: 'reason',
          key: 'reason',
        },
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
                  <a className={styles.operrateColor} onClick={(ev) => this.showModal(ev,record,0)}>审核</a>
                </Menu.Item>
              </Menu>
            );
            let OperateMenu1 = (
              <Menu>
                <Menu.Item>
                  <a className={styles.operrateColor}>更多</a>
                </Menu.Item>
              </Menu>
            );
            return(
              <div>
              <a　onClick={(ev) => {this.detailModal(ev,record)}}>详请</a>
              <Divider type="vertical" />
              <Dropdown overlay={record.status=="0"?OperateMenu:OperateMenu1}>
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

    //转换状态信息
    getStatus1 = (d) => {
      let status = "";
      switch (d) {
        case "0":
          status = "审核中";
          break;
        case "1":
          status = "审核拒绝";
          break;
        case "2":
          status = "微信已受理";
          break;
        case "3":
          status = "微信代付已到账";
          break;
        case "4":
          status = "微信代付失败";
          break;
        case "5":
          status = "微信银行退票";
          break;
      }
      return status;
    }

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
        api: 'withdraw_list',
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
        examineaModalData: record,
      });
    };
  
    handleOk = e => {
      let { examineaModalData } = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
       if (!err) {
          http({
            method: 'post',
            api: 'handle_withdraw',
            data: { 
              auditor_id: this.props.currentUser.billno,
              user_id: examineaModalData.user_id,
              userno: examineaModalData.userno,
              remark: values.remark,
              auditor_phone: this.props.currentUser.userno,
              flag: 1,
            },
          }).then((result) => {
            const { status, msg, data } = result;
            if (status == '0') {
              message.info(msg);
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
    };
  
    handleCancel = e => {
      this.props.form.resetFields();
      this.setState({
        modalVisible: false,
        examineaModalData: {},
      });
    };
    //未通过
    notAdopted = (e) =>{
      let { examineaModalData } = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
       if (!err) {
          http({
            method: 'post',
            api: 'handle_withdraw',
            data: { 
              auditor_id: this.props.currentUser.billno,
              user_id: examineaModalData.user_id,
              userno: examineaModalData.userno,
              remark: values.remark,
              auditor_phone: this.props.currentUser.userno,
              flag: 2,
            },
          }).then((result) => {
            const { status, msg, data } = result;
            if (status == '0') {
              message.info(msg);
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
        // this.getList();
    }

    render(){
      const { getLoading, detailModalData, examineaModalData } = this.state;
      const { Option } = Select;   
      const { getFieldDecorator } = this.props.form;  
      return(
        <div>
          <div  className={styles.example}>
            <Spin spinning={ getLoading } />
          </div>
          <Card>
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
                scroll={{ x: 4000, y: 500 }} 
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
              title="审核"
              visible={this.state.modalVisible}
              onOk={this.handleOk}
              onCancel={this.handleCancel}
            //   width={1000}
              maskClosable={false}
              footer={[
                <Button key="back" onClick={this.handleCancel}>
                  返回
                </Button>,
                <Button type="danger" onClick={this.notAdopted}>
                  拒绝
                </Button>,
                <Button key="submit" type="primary" onClick={this.handleOk}>
                  通过
                </Button>,
              ]}
            >
              <div className={styles.detailContainer}>
                <h3 style={{color:"red",fontWeight:"700",width:"100%",textAlign:"center"}}>提现审核信息</h3>
                <p><span>申请人名称：</span><span className="content">{examineaModalData.name}</span></p>
                <p><span>申请时间：</span><span className="content">{examineaModalData.add_date}</span></p>
                <p><span>微信ID：</span><span className="content">{examineaModalData.wx_id}</span></p>
                <p><span>手机：</span><span className="content">{examineaModalData.mobile}</span></p>
                <p><span>银行编号：</span><span className="content">{examineaModalData.bank_id}</span></p>
                <p><span>银行名称：</span><span className="content">{examineaModalData.bank_name}</span></p>
                <p><span>银行开号：</span><span className="content">{examineaModalData.bank_card_id}</span></p>
                <p><span>持卡人：</span><span className="content">{examineaModalData.cardholder}</span></p>
                <p><span>实际提现金额：</span><span className="content">{examineaModalData.z_money}</span></p>
                <p><span>壹软收取的手续费：</span><span className="content">{examineaModalData.s_charge}</span></p>
                <p><span>状态：</span><span className="content">{this.getStatus1(examineaModalData.status)}</span></p>
                <p><span>审核日期：</span><span className="content">{examineaModalData.check_date}</span></p>
                <p><span>微信大夫成功日期：</span><span className="content">{examineaModalData.payment_time}</span></p>
                <p><span>审核员ID：</span><span className="content">{examineaModalData.auditor_id}</span></p>
                <p><span>到账类型：</span><span className="content">{examineaModalData.type==1?"银行":"微信钱包"}</span></p>
                <p><span>持卡人身份：</span><span className="content">{examineaModalData.user_type==0?"商家":"业务员"}</span></p>
                <p><span>审核原因：</span><span className="content">{examineaModalData.remark}</span></p>
                <p><span>微信代付款失败原因：</span><span className="content">{examineaModalData.reason}</span></p>
              </div>
              <div style={{width:"100%",height:"1px",background:"red",marginBottom:"20px"}}></div>
              <h3 style={{color:"red",fontWeight:"700",width:"100%",textAlign:"center"}}>添加审核信息</h3>
              <Form layout="vertical">
                <Row gutter={24}>
                  <Col>
                    <Form.Item label="审核备注：">
                      {getFieldDecorator('remark', {
                      rules: [{ required: true, message: '请输入审核备注' }],
                      // initialValue: modifyModalData.express_name
                      })(
                      <Input placeholder="请输入审核备注" />
                      )}
                  </Form.Item>
                  </Col>
                </Row>
              </Form>
            </Modal>
          </div>
          {/* 详情 */}
          <Modal
              title="客户详情"
              visible={this.state.detailModalVisible}
              onCancel={this.detailHandleCancel}
            //   width={1000}
              maskClosable={false}
              footer={[
                <Button type='primary' key="back" onClick={this.detailHandleCancel}>
                  关闭
                </Button>,
              ]}
            >
              <div className={styles.detailContainer}>
                <p><span>申请人名称：</span><span className="content">{detailModalData.name}</span></p>
                <p><span>申请时间：</span><span className="content">{detailModalData.add_date}</span></p>
                <p><span>微信ID：</span><span className="content">{detailModalData.wx_id}</span></p>
                <p><span>手机：</span><span className="content">{detailModalData.mobile}</span></p>
                <p><span>银行编号：</span><span className="content">{detailModalData.bank_id}</span></p>
                <p><span>银行名称：</span><span className="content">{detailModalData.bank_name}</span></p>
                <p><span>银行开号：</span><span className="content">{detailModalData.bank_card_id}</span></p>
                <p><span>持卡人：</span><span className="content">{detailModalData.cardholder}</span></p>
                <p><span>实际提现金额：</span><span className="content">{detailModalData.z_money}</span></p>
                <p><span>壹软收取的手续费：</span><span className="content">{detailModalData.s_charge}</span></p>
                <p><span>状态：</span><span className="content">{this.getStatus1(detailModalData.status)}</span></p>
                <p><span>审核日期：</span><span className="content">{detailModalData.check_date}</span></p>
                <p><span>微信大夫成功日期：</span><span className="content">{detailModalData.payment_time}</span></p>
                <p><span>审核员ID：</span><span className="content">{detailModalData.auditor_id}</span></p>
                <p><span>到账类型：</span><span className="content">{detailModalData.type==1?"银行":"微信钱包"}</span></p>
                <p><span>持卡人身份：</span><span className="content">{detailModalData.user_type==0?"商家":"业务员"}</span></p>
                <p><span>审核原因：</span><span className="content">{detailModalData.remark}</span></p>
                <p><span>微信代付款失败原因：</span><span className="content">{detailModalData.reason}</span></p>
              </div>
            </Modal>
        </div>
      );
    }
}

export default ArrivedAccount;