import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker, message } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './Content.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class Content extends PureComponent{
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
          }
         
        }
        this.columns = [
          {
            title: '类型',
            dataIndex: 'c_type',
            key: 'c_type',
            width: 170,
            fixed: 'left',
            render: (text, record) =>{
              let type = "";
              if(record.c_type==0){
                type = "商城产品信息";
              }else if(record.c_type==1){
                type = " 商城图片信息";
              }else if(record.c_type==2){
                type="商城店铺设置";
              }
              return type;
            }
          },
          {
            title: '提交日期',
            width: 200,
            dataIndex: 'billdate',
            key: 'billdate', 
          },
          {
            title: '内容的编号',
            width: 170,
            dataIndex: 'c_billno',
            key: 'c_billno',
          },
          
          {
            title: '审核人名',
            dataIndex: 'c_username', 
            key: 'c_username',
            width: 150,
          },
          {
            title: '审核人编号',
            dataIndex: 'c_userno',
            key: 'c_userno',
            width: 180,
          },
          {
            title: '内容摘要',
            dataIndex: 'c_title',
            key: 'c_title',
            render: (text,record) => {
              let title = record.c_title.substr(0,20);
              return title;
            }
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
                    {/* <a onClick={this.showModal}>审核</a> */}
                    <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,0)}>下架</a>
                    <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,1)}>通过</a>
                    <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,2)}>删除</a>
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

          // //操作菜单
          // this.OperateMenu = (
          //   <Menu>
          //     <Menu.Item>
          //       {/* <a onClick={this.showModal}>审核</a> */}
          //       <a>
          //         更多
          //       </a>
          //     </Menu.Item>
          //   </Menu>
          // );
    }

    confirm = (ev,record,d) => {
      let c_type = "";
      switch(d){
        case 0: 
          c_type = "下架";
          break;
        case 1: 
          c_type = "通过";
          break;
        case 2: 
          c_type = "删除";
          break;
      }
      Modal.confirm({
        title: `内容审核设置为 ${c_type}`,
        content: `你确定要把 “${record.c_title}” 的内容的审核状态设置为 “${c_type}” 吗？`,
        okText: '确认',
        cancelText: '取消',
        onOk: () => this.confirmHandleOk(record,c_type,d),
        onCancel: this.confirmHandleCencle,
      });
    }

    confirmHandleOk = (record,c_type,d) =>{
      http({
        method: 'post',
        api: 'doreview',
        params: {
          billno: record.billno,
          c_username: this.props.currentUser.username,
          c_userno: this.props.currentUser.userno,       
          checked: d,
        },
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          message.info(msg);
          this.getList();
        } else {
          message.info(msg);
        }
      }).catch(() => {
        message.info("操作异常");
      });
    }
    confirmHandleCencle = () => {
      message.info("已取消操作");
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
        api: 'getreviewlist',
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
    showModal = () => {
      this.setState({
        modalVisible: true,
      });
    };
  
    handleOk = e => {
      this.setState({
        status: 2
      }),this.setRealNameList();
    };
  
    handleCancel = e => {
      this.setState({
        modalVisible: false,
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

    //获取类型
    getType = (c_type) => {
      let type = "";
      if(c_type==0){
        type = "商城产品信息";
      }else if(c_type==1){
        type = " 商城图片信息";
      }else if(c_type==2){
        type="商城店铺设置";
      }
      return type;
    }

    render(){
      const { getLoading, detailModalData } = this.state;
      const { Option } = Select;   
        return(
          <PageHeaderWrapper>
            <div>
              <div  className={styles.example}>
                <Spin spinning={ getLoading } />
              </div>
              <Card>
                <RangePicker style={{margin: "0 0 15px 0"}} onChange={this.onChangeRangePicker} />
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
                    scroll={{ x: 1500, y: 500 }} 
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
                      未通过
                    </Button>,
                    <Button key="submit" type="primary" onClick={this.handleOk}>
                      通过
                    </Button>,
                  ]}
                >
                  <div className={styles.detailContainer}>
                    <p><span>用户名：</span><span className="content">{detailModalData.username}</span></p>
                    <p><span>昵称：</span><span className="content">{detailModalData.nickname}</span></p>
                    <p><span>注册时间：</span><span className="content">{detailModalData.regdate}</span></p>
                    <p><span>手机：</span><span className="content">{detailModalData.userno}</span></p>
                    <p><span>电话：</span><span className="content">{detailModalData.tel}</span></p>
                    <p><span>性别：</span><span className="content">{detailModalData.sex}</span></p>
                    <p><span>编号：</span><span className="content">{detailModalData.userno}</span></p>
                    <div className={styles.list}>
                        <div className={styles.left}>认证状态：</div>
                        <div className={styles.right}>{this.state.modalData.styles==1? "正在审核中": "未通过"}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>备注：</div>
                        <div className={styles.right}>
                          <Select
                            showSearch
                            style={{ width: 200 }}
                            placeholder="请选择未通过原因"
                            optionFilterProp="children"
                            onChange={this.onChange}
                            onFocus={this.onFocus}
                            onBlur={this.onBlur}
                            onSearch={this.onSearch}
                            filterOption={(input, option) =>
                              option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
                            }
                          >
                            <Option value="证件照片模糊">证件照片模糊</Option>
                            <Option value="证件信息与认证姓名不一致">证件信息与认证姓名不一致</Option>
                            {/* <Option value="">Tom</Option> */}
                          </Select>
                        </div>
                    </div>
                    
                  </div>
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
                    <Button type="primary" key="back" onClick={this.detailHandleCancel}>
                      关闭
                    </Button>,
                  ]}
                >
                    <div className={styles.detailContainer}>
                    <p><span>类型：</span><span className="content">{this.getType(detailModalData.c_type)}</span></p>
                    <p><span>提交日期：</span><span className="content">{detailModalData.billdate}</span></p>
                    <p><span>内容编号：</span><span className="content">{detailModalData.c_billno}</span></p>
                    <p><span>审核人：</span><span className="content">{detailModalData.c_username}</span></p>
                    <p><span>审核人编号：</span><span className="content">{detailModalData.c_userno}</span></p>
                    <p><span>内容摘要：</span><span className="content">{detailModalData.c_title}</span></p>
                    {/* <p><span>编号：</span><span className="content">{detailModalData.userno}</span></p> */}
                    {/* <p><span>报销日期：</span><span className="content">{detailModalData.reimburdate}</span></p>
                    <p><span>图片一：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image1} alt="img" /></span></p>
                    <p><span>图片二：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image2} alt="img"/></span></p>
                    <p><span>图片三：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image3} alt="img"/></span></p>
                    <p><span>报销描述：</span><span className="content">{detailModalData.describe}</span></p>
                    <p><span>状态：</span><span className="content">{this.getStatus(detailModalData.status)}</span></p>
                    <p><span>团队号：</span><span className="content">{detailModalData.admin}</span></p>
                    <p><span>队长签名：</span><span className="content"><img style={{width:"180px",height:"180px"}} src={detailModalData.captainsign} alt="img"/></span></p>
                    <p><span>老板签名：</span><span className="content"><img style={{width:"180px",height:"180px"}} src={detailModalData.bosssign} alt="img"/></span></p>
                    <p><span>审核理由：</span><span className="content">{detailModalData.reason}</span></p> */}
                    </div>
                </Modal>
            </div>
          </PageHeaderWrapper>
        );
    }
}



export default Content;