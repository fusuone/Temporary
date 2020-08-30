import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './Statistics.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class Statistics extends PureComponent{
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
              title: '用户名',
              width: 170,
              dataIndex: 'username',
              key: 'username',
              fixed: 'left',
            },
            {
              title: '添加日期',
              width: 200,
              dataIndex: 'regdate',
              key: 'regdate',
              fixed: 'left',   
            },
            {
                title: '手机',
                dataIndex: 'userno',
                key: 'userno',
                width: 170,
            },
            // {
            //   title: '昵称',
            //   dataIndex: 'nickname', 
            //   key: 'nickname',
            //   width: 150,
            // },
            // {
            //   title: '性别',
            //   dataIndex: 'sex',
            //   key: 'sex',
            //   width: 100,
            // },
            // {
            //   title: '电话',
            //   dataIndex: 'tel',
            //   key: 'tel',
            //   width: 170,
            // },
            {
              title: '用户编号',
              dataIndex: 'userno',
              key: 'userno',
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
                    <span className="ant-dropdown-link" href="#">
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
                {/* <a onClick={this.showModal}>审核</a> */}
                <a>
                  更多
                </a>
              </Menu.Item>
            </Menu>
          );
    }

    


    componentDidMount() {
      // this.getList();
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
        api: 'getintent',
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
        // this.getList();
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
                    scroll={{ x: 1800, y: 500 }} 
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
                  title="客户详情"
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
                    <p><span>用户名：</span><span className="content">{detailModalData.username}</span></p>
                    <p><span>昵称：</span><span className="content">{detailModalData.nickname}</span></p>
                    <p><span>注册时间：</span><span className="content">{detailModalData.regdate}</span></p>
                    <p><span>手机：</span><span className="content">{detailModalData.userno}</span></p>
                    <p><span>电话：</span><span className="content">{detailModalData.tel}</span></p>
                    <p><span>性别：</span><span className="content">{detailModalData.sex}</span></p>
                    <p><span>编号：</span><span className="content">{detailModalData.userno}</span></p>
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



export default Statistics;