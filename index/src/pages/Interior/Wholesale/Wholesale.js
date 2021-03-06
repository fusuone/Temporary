import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker,Popconfirm, Radio, Switch, message } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './Wholesale.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class Wholesale extends PureComponent{
  constructor(props){
    super(props);
    this.state={
      getLoading: true,
      RealNameDate : [],
      RealName_billno: "",
      modalVisible: false, //modal
      status: -10,
      modalData: {}, 
      detailModalVisible: false, //详情页面显示状态
      detailModalData: {},
      total: '',
      reqParams: {
        allow: 0,
        page: 1,
        pagesize: 15,
        // begin: '',
        // end: '',
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
        title: '注册日期',
        width: 200,
        dataIndex: 'regdate',
        key: 'regdate', 
      },
      {
        title: '手机',
        dataIndex: 'userno',
        key: 'userno',
        width: 170,
      },
      {
        title: '公司',
        dataIndex: 'company',
        key: 'company',
        width: 200,
      },
      {
        title: '公司联系人',
        dataIndex: 'company_linkman',
        key: 'company_linkman',
        width: 170,
      },
      {
        title: '省',
        dataIndex: 'province',
        key: 'province',
        width: 170,
      },
      {
        title: '市',
        dataIndex: 'city',
        key: 'city',
        width: 170,
      },
      {
        title: '镇',
        dataIndex: 'town',
        key: 'town',
        width: 170,
      },
      {
        title: '街道',
        dataIndex: 'street',
        key: 'street',
        width: 170,
      },
      {
        title: '审核状态',
        dataIndex: 'allow',
        key: 'allow',
        width: 170,
        render: (text,record) => {
          let a =  this.getAllow(record.allow);
          return a;
        }
      },
      {
        title: '用户编号',
        dataIndex: 'billno',
        key: 'billno',
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
                <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,1)}>通过</a>
                <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,2)}>不通过</a>
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

  confirmHandleOk = (record,d) => {
    this.setCalltxtStatus(record,d);
  }

  confirmHandleCencle = () => {
    message.info('已取消操作');
  }

  //审核状态
  getAllow = (d) => {
    let txt = "";
    switch(d){
      case "0":
        txt="待审核";
        break;
      case "1":
        txt="审核通过";
        break;
      case "2":
        txt="审核不通过";
        break;
    }
    return txt;
  }

  confirm = (ev,record,d) => {
    let calltxt = "";
    switch(d){
      case 1: 
        calltxt = "通过";
        break;
      case 2: 
        calltxt = "不通过";
        break;
    }
    Modal.confirm({
      title: `货真真用户审核状态设置为 ${calltxt}`,
      content: `你确定要把手机号为 ${record.userno} 的审核状态设置为 “${calltxt}” 吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.confirmHandleOk(record,d),
      onCancel: this.confirmHandleCencle,
    });
  }


  //设置跟进状态
  setCalltxtStatus = (record,d) => {
    let allow = d;
    http({
      method: 'post',
      api: 'dowholesale',
      params: { 
        userno: this.props.currentUser.userno,
        billno: record.billno,
        allow: allow,
      },
    }).then((result) => {
      const { status, msg, data } = result;
      if (status == '0') {
        message.info('修改成功');
        this.getList();
      } else {
        message.info(msg);
      }
    }).catch(() => {
      message.info('操作失败');
    });
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
    let { reqParams } = this.state;
    http({
      method: 'post',
      api: 'getwholesalelist',
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

  //未通过原因 
  onChange = (value) => {
  }

  onBlur = () => {
  }

  onFocus = () => {
  }

  onSearch = (val) => {
  }

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

  //搜索
  searchData = () => {
    this.getList();
  }

  switchChecked = (e) => {
    if(e==true){
      this.state.reqParams.allow=1;
    }else if(e==false){
      this.state.reqParams.allow=0;
    }
    this.getList();
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
            {/* <RangePicker style={{margin: "0 0 15px 0"}} onChange={this.onChangeRangePicker} /> */}
            <Switch
              style={{margin: "15px"}}
              checkedChildren={<span><Icon type="check" />已审核用户</span>}
              unCheckedChildren={<span><Icon type="close" />未审核用户</span>}
              // defaultChecked
              onChange={this.switchChecked}
            />
            {/* <Radio style={{margin: "0 15px"}}></Radio> */}
            {/* <Divider type="vertical" /> */}
            {/* <Button style={{margin: "0 15px"}} type="primary" onClick={this.searchData}>搜索</Button> */}
            <Table 
            // pageSize={15}
                onRow={record => {
                return {
                    onClick: event => {
                    this.setState({
                        modalData: record,
                        RealName_billno: record.billno
                    });
                    }, // 点击行
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
              title="审核"
              visible={this.state.modalVisible}
              onOk={this.handleOk}
              onCancel={this.handleCancel}
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
            maskClosable={false}
            footer={[
              <Button type="primary" key="back" onClick={this.detailHandleCancel}>
                关闭
              </Button>
            ]}
          >
            <div className={styles.detailContainer}>
            <p><span>用户名：</span><span className="content">{detailModalData.username}</span></p>
            <p><span>注册时间：</span><span className="content">{detailModalData.regdate}</span></p>
            <p><span>手机：</span><span className="content">{detailModalData.userno}</span></p>
            <p><span>公司：</span><span className="content">{detailModalData.company}</span></p>
            <p><span>公司联系人：</span><span className="content">{detailModalData.company_linkman}</span></p>
            <p><span>地址：</span><span className="content">{detailModalData.province+detailModalData.city+detailModalData.town+detailModalData.street}</span></p>
            <p><span>审核状态：</span><span className="content">{this.getAllow(detailModalData.allow)}</span></p>
            </div>
          </Modal>
        </div>
      </PageHeaderWrapper>
    );
  }
}


export default Wholesale;