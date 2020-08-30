import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './MyselfExamine.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class MyselfExamine extends PureComponent{
    constructor(props){
        super(props);
        this.state={
          getLoading: true,
          RealNameDate : [],
          RealName_billno: "",
          modalVisible: false, //modal
          status: -10,
          modalData: {}, 
          ////////////////////
          detailModalVisible: false, //详情页面显示状态
          detailModalData: {},
        }
        this.columns = [
            {
              title: '姓名',
              width: 120,
              dataIndex: 'username',
              key: 'username',
              fixed: 'left',
            },
            {
              title: '用户号',
              width: 120,
              dataIndex: 'userno',
              key: 'userno',
              fixed: 'left',   
            },
            {
              title: '团队号',
              dataIndex: 'admin',
              key: 'admin',
              width: 150,
            },
            {
              title: '项目名称',
              dataIndex: 'projectname',
              key: 'projectname',
              width: 150,
            },
            {
              title: '金额',
              dataIndex: 'money',
              key: 'money',
              width: 150,
            },
            {
              title: '报销类型',
              dataIndex: 'type',
              key: 'type',
              width: 100,
            },
            {
              title: '报销日期',
              dataIndex: 'reimburdate',
              key: 'reimburdate',
              width: 150,
            },
            {
              title: '图片1',
              dataIndex: 'image1',
              key: 'image1',
              width: 150,
              render: (text, record) => {
                return(
                  <img style={{width:80,height:50}} src={record.image1} />
                );
              }
            },
            {
                title: '图片2',
                dataIndex: 'image2',
                key: 'image2',
                width: 150,
                render: (text, record) => {
                    return(
                    <img style={{width:80,height:50}} src={record.image2} />
                    );
                }
            },
            {
                title: '图片3',
                dataIndex: 'image3',
                key: 'image3',
                width: 150,
                render: (text, record) => {
                  return(
                    <img style={{width:80,height:50}} src={record.image3} />
                  );
                }
            },
            {
              title: '状态',
              dataIndex: 'status',
              key: 'status',
              width: 100,
              render: (text, record) => {
                // -1无效,0未提交,1已提交(审核中),2通过,3不通
                let status = ""
                if(record.status == 0){
                  status = "未提交";
                }else if(record.status == 1){
                  status = "已提交(审核中)";
                }else if (record.status == 2){
                    status = "通过";
                }else if (record.status == 3){
                    status = "不通过";
                }else if( record.status == -1){
                    status = "无效";
                }
                return(
                  <div>{status}</div>
                );
              }
            },
            {
                title: '队长签名',
                dataIndex: 'captainsign',
                key: 'captainsign',
                width: 150,
                render: (text, record) => {
                  return(
                    <img style={{width:80,height:50}} src={record.captainsign} />
                  );
                }
            },
            {
                title: '老板签名',
                dataIndex: 'bosssign',
                key: 'bosssign',
                width: 150,
                render: (text, record) => {
                  return(
                    <img style={{width:80,height:50}} src={record.bosssign} />
                  );
                }
            },
            {
              title: '审核理由',
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
                return(
                  <div>
                  <a　onClick={(ev) => {this.detailModal(ev,record)}}>详请</a>
                  <Divider type="vertical" />
                  <Dropdown overlay={this.OperateMenu}>
                    <span style={{color:"#1890ff"}} className="ant-dropdown-link" href="#">
                      更多 <Icon type="down" />
                    </span>
                  </Dropdown>
                </div>
                );
              }
                
            },
          ];

          //操作菜单
          this.OperateMenu = (
            <Menu>
              <Menu.Item>
                <a className={styles.operrateColor} onClick={this.showModal}>审核</a>
                <a className={styles.operrateColor}>更多</a>
              </Menu.Item>
            </Menu>
          );
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
      http({
        method: 'post',
        api: 'getreimbur',
        params: {
            uid: currentUser.userno,
            admin: currentUser.admin,
            flag: "0",
        }
      }).then((result) => {
        const { status, msg, data } = result;
        // console.log(data);
        if (status === '0') {
          this.setState({
            RealNameDate: data.list,
            getLoading: false
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
      // console.log(this.state.RealName_billno);
      this.setState({
        modalVisible: true,
      });
    };
  
    handleOk = e => {
      // console.log(this.state.modalData["billno"]);
      this.setState({
        // modalVisible: false,
        status: 2
      }),this.setRealNameList();
      // console.log("审核通过");
    };
  
    handleCancel = e => {
      // console.log("返回");
      this.setState({
        modalVisible: false,
      });
    };
    //未通过
    notAdopted = (e) =>{
      // console.log("未通过");
      this.setState({
        status: -1
      }),this.setRealNameList();
    }
    //////////////


    ///未通过原因 

    onChange = (value) => {
      console.log(`selected ${value}`);
    }

    onBlur = () => {
      console.log('blur');
    }

    onFocus = () => {
      console.log('focus');
    }

    onSearch = (val) => {
      console.log('search:', val);
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
        // console.log(data);
        if (status === '0') {
          this.setState({ 
            getLoading: false,
            modalVisible: false,
            status: -10
          }),this.getList();
          // console.log("审核数据成功");
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
            status: -10
            // modalVisible: false
          });
          // console.log("审核数据失败");
        }
      }).catch(() => {
        this.setState({ getLoading: false, status: -10 });
      });
    }


    /////////////////////////////////////////////////
    //详情modal
    detailModal  =(ev,data) =>{
        console.log(data);
        this.setState({
            detailModalData: data,
            detailModalVisible: true
        });
      //  setTimeout(()=>{
      //   let spanArray = document.getElementsByClassName("content");
      //   for(let i=0;i<(spanArray.length);i++){
      //     console.log(spanArray[i].clientHeight);
      //   }
      //   console.log(spanArray);
      //  },2000)
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




    render(){
      const { getLoading, detailModalData } = this.state;
      const { Option } = Select;
          
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
                      // console.log(record);

                      this.setState({
                        modalData: record,
                        RealName_billno: record.billno
                      });
                    }, // 点击行
                    // onDoubleClick: event => {},
                    // onContextMenu: event => {},
                    // onMouseEnter: event => {}, // 鼠标移入行
                    // onMouseLeave: event => {},
                  };
                }}
                columns={this.columns} dataSource={this.state.RealNameDate} scroll={{ x: 2400, y: 300 }} />
              </Card>
              {/* modal */}
              <div>
                <Modal
                  title="实名审核"
                  visible={this.state.modalVisible}
                  onOk={this.handleOk}
                  onCancel={this.handleCancel}
                  width={1000}
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
                  <div className={styles.RealNameContent}>
                    {/* <div className={styles.list}>
                        <div className={styles.left}>真正姓名(法定代表人)：</div>
                        <div className={styles.right}>{this.state.modalData.real_name}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>身份选择：</div>
                        <div className={styles.right}>{this.state.modalData.work_style==1? "企业": "个人"}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>身份证正面：</div>
                        <div className={styles.right}>
                          <img src={this.state.modalData.card_img1} />
                        </div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>身份证反面：</div>
                        <div className={styles.right}>
                          <img src={this.state.modalData.card_img2} />
                        </div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>手拿身份证：</div>
                        <div className={styles.right}>
                          <img src={this.state.modalData.card_img3} />
                        </div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>营业执照：</div>
                        <div className={styles.right}>
                          <img src={this.state.modalData.license_img} />
                        </div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>公司名称：</div>
                        <div className={styles.right}>{this.state.modalData.company}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>营业执照号：</div>
                        <div className={styles.right}>{this.state.modalData.license_no}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>身份证号：</div>
                        <div className={styles.right}>{this.state.modalData.card_no}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>性别：</div>
                        <div className={styles.right}>{this.state.modalData.sex==0? "男":"女"}</div>
                    </div>
                    <div className={styles.list}>
                        <div className={styles.left}>身份证到期日期：</div>
                        <div className={styles.right}>{this.state.modalData.end_date}</div>
                    </div> */}
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
                  title="报销单详情"
                  visible={this.state.detailModalVisible}
                  onCancel={this.detailHandleCancel}
                //   width={1000}
                  maskClosable={false}
                  footer={[
                    <Button key="back" onClick={this.detailHandleCancel}>
                      关闭
                    </Button>,
                  ]}
                >
                    <div className={styles.detailContainer}>
                    <p><span>姓名：</span><span className="content">{detailModalData.username}</span></p>
                    <p><span>用户号：</span><span className="content">{detailModalData.userno}</span></p>
                    <p><span>提交时间：</span><span className="content">{detailModalData.billdate}</span></p>
                    <p><span>团队号：</span><span className="content">{detailModalData.admin}</span></p>
                    <p><span>项目名称：</span><span className="content">{detailModalData.projectname}</span></p>
                    <p><span>金额：</span><span className="content">{detailModalData.money}</span></p>
                    <p><span>报销类型：</span><span className="content">{detailModalData.type}</span></p>
                    <p><span>报销日期：</span><span className="content">{detailModalData.reimburdate}</span></p>
                    <p><span>图片一：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image1} alt="img" /></span></p>
                    <p><span>图片二：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image2} alt="img"/></span></p>
                    <p><span>图片三：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.image3} alt="img"/></span></p>
                    <p><span>报销描述：</span><span className="content">{detailModalData.describe}</span></p>
                    <p><span>状态：</span><span className="content">{this.getStatus(detailModalData.status)}</span></p>
                    <p><span>团队号：</span><span className="content">{detailModalData.admin}</span></p>
                    <p><span>队长签名：</span><span className="content"><img style={{width:"180px",height:"180px"}} src={detailModalData.captainsign} alt="img"/></span></p>
                    <p><span>老板签名：</span><span className="content"><img style={{width:"180px",height:"180px"}} src={detailModalData.bosssign} alt="img"/></span></p>
                    <p><span>审核理由：</span><span className="content">{detailModalData.reason}</span></p>
                    </div>
                </Modal>
            </div>
        );
    }
}



export default MyselfExamine;