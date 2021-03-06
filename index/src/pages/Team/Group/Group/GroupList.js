/* eslint-disable react/no-unused-state */
import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';

import { Row, Col, Button, Spin, Card, Select, Popconfirm, Table, Divider, Form, message, Modal, DatePicker, Calendar, Badge, Dropdown, Menu, Icon } from 'antd';
import moment from 'moment';
import SelectGroup from '@/cps/SelectComponents/SelectGroup';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import config from '@/common/config';
import styles from './SetOut.less';
import RuleAdd from './RuleAdd';
import Record from '../../Record/Record';

const { Option } = Select;
const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  custname: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CrudeIn extends PureComponent {
  constructor(props) {
    super(props);
    this.state={
      detailModalVisible:false,
      detailModalData: {},
    }
    this.columns = [{
      title: '头像',
      dataIndex: 'image',
      width: 170,
      render: (text, record) => {
        return (
          <img style={{ width:"80px",height:"80px" }} src={record.image} alt="图片" />
        );
      }
    }, {
      title: '人员名称',
      dataIndex: 'username',
      width: 170
    },
    {
      title: '电话',
      dataIndex: 'tel',
      width: 200
    },
    {
      title: '手机',
      dataIndex: 'tel',
      width: 200
    },
    {
      title: '性别',
      dataIndex: 'sex',
      width: 170
    },
    {
      title: '年龄',
      dataIndex: 'age',
      width: 150,
    },
      {
        title: '邮箱',
        dataIndex: 'mail',
        width: 230,
      },
     {
      title: '团队名称',
      dataIndex: 'teamname',
      width: 170
    },
    {
      title: '添加时间',
      dataIndex: 'billdate',
      width: 170
    }, {
      title: '部门职位',
      dataIndex: 'job',
      width: 170
    }, {
      title: '工号',
      dataIndex: 'jobnumber',
      width: 170
    },
    {
      key: '_fill_'
    }, {
      title: '操作',
      key: 'action',
      fixed: 'right',
      width: 120,
      render: text => {
        let menu=(
          <Menu>
            <Menu.Item>
                <a width="20" icon="edit" onClick={() => this.gotoEdit(text)}>编辑</a>
                <a width="20" icon="delete">删除</a>
            </Menu.Item>
          </Menu>
        );
        return (
          <Fragment>
            <a width="20" icon="detail" onClick={() => this.goToRecord(text)} >考勤</a>
            
            <Divider type="vertical" />
            <Dropdown overlay={menu}>
              <a className="ant-dropdown-link">
                更多 <Icon type="down" />
              </a>
            </Dropdown>
          </Fragment>
        )
      }
    }];


    this.state = {
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        page: 1,
        uid: props.currentUser.userno,
        userno: props.currentUser.userno,
        admin: props.currentUser.admin,
        ...initialSearchParams
      },
      selectedRowKeys: [],
      getLoading: false,
      submitting: false,
      // eslint-disable-next-line react/no-unused-state
      showRuleAdd: false,
      // eslint-disable-next-line react/no-unused-state
      addOrEdit: null,
      activeItem: {},
      auditFlag: null, // 是否在选择审核处理
      visibleRecord: false, //是否显示考勤modal
      RecordData: [], //考勤记录数据
      RecordModeltitle: "考勤记录"
    };

    props.getContext && props.getContext(this);
  }

  //更多菜单
getMenu = () =>{
  let menu=(
    <Menu>
      <Menu.Item>
          <Popconfirm title="是否要删除此行？">
            <a width="20" icon="delete">删除</a>
          </Popconfirm>
          {/* <Button width="20" icon="detail" onClick={() => this.gotoEdit(text)} >详情</Button> */}
          <Divider type="vertical" />
          <a width="20" icon="detail" onClick={() => this.goToRecord(text)} >考勤</a>
      </Menu.Item>
    </Menu>
  );
  return menu;
}

  //去考勤
  goToRecord = (userData) => {
    this.setState({
      RecordModeltitle: userData.username +"的考勤记录"
    });
    this.getRecordData(userData.userno),this.showModalRecord();
  }
  

  getisteam = (d) => {
    let txt = "";
    // -3普通队员, -2副经理（秘书助手）、 -1 经理、0代理、1队员（销售类)、2财务、3 仓库、4 送货 5生产 6 技术，7 文员、8其它、9批发商
    switch(d){
      case -3:
        txt="普通队员";
        break;
    }
    return txt;
  }


  showModalRecord = () => {
    this.setState({
      visibleRecord: true,
    });
  };


  handleOkRecord = e => {
    this.setState({
      visibleRecord: false,
    });
  };


  handleCancelRecord = e => {
    this.setState({
      visibleRecord: false,
    });
  };


  // 获取考勤数据
  getRecordData = (userno) => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'post',
      api: 'getattenddetail',
      params: {
        userno: userno,
        ispaging: "2",
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          RecordData: data.list,
          getLoading: false
        });
      } else {
        message.warn(msg);
        this.setState({
          RecordData: [],
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }


  componentDidMount() {
    this.getList();
  }

  // 搜索
  onSearch = (e) => {
    e.preventDefault();
    const { form } = this.props;
    form.validateFields((err, fieldsValue) => {
      if (err) return;
      this.changeReqParams({
        page: 1,
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        custname: fieldsValue.custname
      }, this.getList);
    });
  }


  // 刷新
  onRefresh = (type) => {
    if (type === 'reset') {
      this.props.form.resetFields();
      this.changeReqParams({
        page: 1,
        ...initialSearchParams
      }, this.getList);
    } else {
      this.getList();
    }
  }

  // 录入码单
  addTrack = () => {
    //
  }

  // 删除
  onDelete = () => {
    const { submitting, selectedRowKeys } = this.state;
    if (submitting) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，确定删除？`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk: () => {
          this.setState({ submitting: true });
          http({
            method: 'get',
            api: 'delcrude',
            params: { items: selectedRowKeys.join(',') }
          }).then((result) => {
            const { status, msg } = result;
            if (status === '0') {
              message.success(msg);
              this.onRefresh('reset');
            } else {
              message.warn(msg);
            }
          }).catch(() => {
            //
          }).then(() => {
            this.setState({ submitting: false });
          });
        }
      });
    }
  }

  // 审核
  onAudit = () => {
    const { submitting, selectedRowKeys, admiss } = this.state;
    if (submitting) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，进行${admiss === 'admiss' ? '反审' : '审核'}？`,
        okText: '确认',
        cancelText: '取消',
        onOk: () => {
          this.setState({ submitting: true });
          http({
            method: 'get',
            api: 'checkcrude',
            params: {
              admin: this.props.currentUser.admin,
              billno: selectedRowKeys.join(','),
              // eslint-disable-next-line no-undef
              audit: auditFlag === 'audited' ? '0' : '1'
            }
          }).then((result) => {
            const { status, msg } = result;
            if (status === '0') {
              message.success(msg);
              this.onRefresh('reset');
            } else {
              message.warn(msg);
            }
          }).catch(() => {
            //
          }).then(() => {
            this.setState({ submitting: false });
          });
        }
      });
    }
  }


  // 去新增
  gotoAdd = () => {
    this.setState({
      showRuleAdd: true,
      addOrEdit: '0',
      activeItem: {}
    });
  }

  
  // 去编辑
  gotoEdit = () => {
    this.setState({
      showRuleAdd: true,
      addOrEdit: '1',
      activeItem: {}
    });
  }

  togglePage = (type, item) => {
    const { togglePage } = this.props;
    togglePage && togglePage(type, item);
  }

  handleTableChange = (pagination) => {
    this.changeReqParams({
      page: pagination.current
    }, this.getList);
  }

  // 选中行
  handleRowSelectChange = (selectedRowKeys) => {
    this.setState({ selectedRowKeys });
  }

  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [], auditFlag: null });
  }

  // 重置搜索表单
  handleSearchReset = () => {
    this.props.form.resetFields();
    this.changeReqParams({
      ...initialSearchParams
    }, this.getList);
  }

  // 修改所需的请求参数
  changeReqParams = (params, cb = () => null) => {
    this.setState(prveState => ({
      reqParams: {
        ...prveState.reqParams,
        ...params
      }
    }), cb);
  }

  // 获取数据
  getList = () => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.clearRowSelect();
    this.setState({ getLoading: true });
    http({
      method: 'post',
      api: 'getgroupuserlist',
      params: {
        ...reqParams,
        begindate: reqParams.begindate.format('YYYY-MM-DD'),
        enddate: reqParams.enddate.format('YYYY-MM-DD')
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          listData: {
            list: data.list,
            total: Number(data.total)
          },
          getLoading: false
        });
      } else {
        message.warn(msg);
        this.setState({
          listData: {
            list: [],
            total: 0
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }


  getListData = (value) => {
    let listData;
    let date1 = moment(value).format('YYYY-MM-DD');
    this.state.RecordData.map((value,key) => {
    let billdate = moment(value["billdate"]).format('YYYY-MM-DD');
    if (billdate == date1){
        let startcheckwork = value["startcheckwork"];
        let startworktime = moment(value["startworktime"]).format('hh:mm:ss');
        let endcheckwork = value["endcheckwork"];
        let endworktime = moment(value["endworktime"]).format('HH:mm:ss');
        let sb_type = ' ';
        let xb_type = ' ';
        if(startcheckwork == 1){
          if(startworktime >= "09:00:00"){
            sb_type = 'warning';
          }else{
            sb_type = 'success';
          }
        }
        if(endcheckwork == 1){
          if(endworktime <= "18:00:00"){
            xb_type = 'warning';
          }else{
            xb_type = 'success';
          }
        }
        if(startcheckwork == 0){
          startworktime = "没有记录";
          sb_type = 'error';
        }
        if(endcheckwork == 0){
          endworktime = "没有记录";
          xb_type = 'error';
        }
        listData = [
          { type: sb_type, content: '签到' },
          { type: ' ', content: startworktime },
          { type: xb_type, content: '签退' },
          { type: ' ', content: endworktime },
        ];
      }    
    });
    return listData || [];
  }

  dateCellRender=(value) =>{
    const listData = this.getListData(value);
    return (
      <ul className={styles.events}>
        {listData.map(item => (
          <li key={item.content}>
            <Badge status={item.type} text={item.content} />
          </li>
        ))}
      </ul>
    );
  }

  renderSearch() {
    const {
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col md={8} sm={24}>
              <Form.Item label="制单日期">
                {getFieldDecorator('date', {
                  initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                  rules: [{ required: true, message: '请选择日期' }]
                })(
                  <DatePicker.RangePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="我的团队(队长)">
                {getFieldDecorator('custname')(
                  <SelectGroup />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <span className={styles.submitButtons}>
                <Button icon="search" type="primary" htmlType="submit">
                  查询
                </Button>
                <Button style={{ marginLeft: '8px' }} onClick={this.handleSearchReset}>
                  重置
                </Button>
              </span>
            </Col>
          </Row>
        </Form>
      </div>
    );
  }

  renderOperator() {
    return (
      <div className={styles.tableListOperator}>
        <Select value="操作" type="primary" onClick={this.gotoAdd}>
          <Option value="编辑">编辑</Option>
          <Option value="详情">详情</Option>
          <Option value="删除" onClick={this.onDelete}>删除</Option>
        </Select>
        <Button icon="plus" type="primary" onClick={this.gotoAdd} style={{ marginRight: '30' }}>
          添加队员
        </Button>
        <Button icon="edit" type="primary" onClick={this.gotoEdit}>
          管理队员
        </Button>
        <Button icon="bang">退出团队</Button>
      </div>
    );
  }
  consoled = (m) =>{
    const { RecordData } = this.state;
    let date1 = moment(m).format('YYYY-MM-DD');
    this.state.modal_dtae = date1;
    RecordData.map((v,k)=>{
      let R_data = moment(v.billdate).format('YYYY-MM-DD');
      if(date1==R_data){
        this.setState({
          detailModalData: v,
        });
        this.detailModal(v);
      }
    });   
  }
  //详情modal
  detailModal  =(data) =>{
    this.setState({
      detailModalData: data,
      detailModalVisible: true,
    });
  }

  detailHandleCancel = (e) => {
    this.setState({
        detailModalVisible: false,
        detailModalData: {},
    });
  }
  render() {
    const {detailModalData, modal_dtae,listData, reqParams, selectedRowKeys, getLoading, submitting, auditFlag } = this.state;
    return (
      <PageHeaderWrapper>
        <div className={styles.tableList}>
          <Spin spinning={getLoading || submitting}>
            <Card bordered={false}>
              {this.renderSearch()}
              {this.renderOperator()}
              <Table
                scroll={{ x: 2500, y: 350 }}
                rowKey="id"
                size="middle"
                columns={this.columns}
                dataSource={listData.list}
                onChange={this.handleTableChange}
                rowSelection={{
                  selectedRowKeys,
                  onChange: this.handleRowSelectChange,
                  hideDefaultSelections: true,
                  fixed: true,
                  getCheckboxProps: (record) => {
                    if (auditFlag === 'audited') {
                      return {
                        disabled: record.audit === '0'
                      };
                    }
                    if (auditFlag === 'unaudited') {
                      return {
                        disabled: record.audit === '1'
                      };
                    }
                    return false;
                  },
                  selections: [{
                    key: 'audited',
                    text: '已审核的',
                    onSelect: () => {
                      const keys = [];
                      listData.list.forEach((v) => {
                        v.audit === '1' && keys.push(v.id);
                      });
                      this.setState({ selectedRowKeys: keys, auditFlag: 'audited' });
                      // eslint-disable-next-line indent
                  }
                  }, {
                    key: 'unaudited',
                    text: '未审核的',
                    onSelect: () => {
                      const keys = [];
                      listData.list.forEach((v) => {
                        v.audit === '0' && keys.push(v.id);
                      });
                      this.setState({ selectedRowKeys: keys, auditFlag: 'unaudited' });
                    }
                  }, {
                    key: 'cancelaudit',
                    text: '取消',
                    onSelect: () => {
                      this.setState({ selectedRowKeys: [], auditFlag: null });
                    }
                  }]
                }}
                pagination={{
                  total: listData.total,
                  current: reqParams.page,
                  defaultCurrent: 1,
                  defaultPageSize: config.defaultPageSize
                }}
              />
            </Card>
          </Spin>
          <RuleAdd
            visible={this.state.showRuleAdd}
            addOrEdit={this.state.addOrEdit}
            activeItem={this.state.activeItem}
            handleRefresh={() => this.onRefresh('reset')}
            handleVisible={bool => this.setState({ showRuleAdd: bool })}
          />
        </div>
        {/* 考勤 */}
        <div>
        <Modal
          title={this.state.RecordModeltitle}
          width={1000}
          visible={this.state.visibleRecord}
          onOk={this.handleOkRecord}
          maskClosable={false}
          onCancel={this.handleCancelRecord}
        >
          <Col>
            <Badge status="error" text="没有打卡记录" />
            <Divider type="vertical" />
            <Badge status="warning" text="迟到/早退" />
            <Divider type="vertical" />
            <Badge status="success" text="打卡正常" />
          </Col>
          <Card>
            <Calendar
              dateCellRender={this.dateCellRender}
              monthCellRender={this.monthCellRender} 
              onSelect={this.consoled}
            />
          </Card>

        </Modal>
        <Modal
          title={`${modal_dtae}的考勤详情`}
          visible={this.state.detailModalVisible}
          onCancel={this.detailHandleCancel}
          maskClosable={false}
          footer={[
            <Button type="primary" key="back" onClick={this.detailHandleCancel}>关闭</Button>
          ]}
        >
          <div className={styles.detailContainer}>
            <p><span>上午签到时间：</span><span className="content">{detailModalData!=undefined? detailModalData.startworktime:null}</span></p>
            <p><span>上午签到地点：</span><span className="content">{detailModalData!=undefined? detailModalData.startworkaddress:null}</span></p>
            <p><span>下午签到时间：</span><span className="content">{detailModalData!=undefined? detailModalData.endworktime:null}</span></p>
            <p><span>下午签到地点：</span><span className="content">{detailModalData!=undefined? detailModalData.endworkaddress:null}</span></p>
          </div>
        </Modal>
      </div>
      </PageHeaderWrapper>
    );
  }
}

export default CrudeIn;
