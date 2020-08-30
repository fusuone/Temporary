import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker, Input, Form, Row, Col, message } from 'antd';
import { reduce } from 'zrender/lib/core/util';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import styles from './PreferentialActivity.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
const { RangePicker } = DatePicker;
const { Search } = Input;


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class PreferentialActivity extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: false,
      RealNameDate: [],
      RealName_billno: '',


      modalVisible: false, // modal
      status: -10,
      modalData: {},
      // //////////////////
      detailModalVisible: false, // 详情页面显示状态
      detailModalData: {},
      total: '',
      reqParams: {
        userno: '',
        page: 1,
        pagesize: 15,
        begin: '',
        end: '',
        searchkey: ''
      },
      modifyModalData: {}

    };
    this.columns = [
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        fixed: 'left',
        render: (text, record) => {
          return (
                <img style={{ width: 100, height: 60 }} src={record.image1} />
          );
        }
      },
      {
        title: '商品名称',
        width: 150,
        dataIndex: 'waresname',
        key: 'waresname'
      },
      {
        title: '商品编码',
        dataIndex: 'productno',
        key: 'productno',
        width: 150
      },
      {
        title: '商品类型',
        dataIndex: 'type',
        key: 'type',
        width: 120,
        render: (text, record) => {
          return (
                <div>
                 {record.type == 0 ? '进销的产品':'生产的产品'}
               </div>
          );
        }
      },
      {
        title: '商城在售',
        dataIndex: 'onsale',
        key: 'onsale',
        width: 120,
        render: (text, record) => {
          return (
                <div>
                 {record.type == 0 ? '下架不在售':'上架在售'}
               </div>
          );
        }
      },
      {
        title: '库存数',
        dataIndex: 'qty',
        key: 'qty',
        width: 100
      },
      {
        title: '次品',
        dataIndex: 'qty1',
        key: 'uniqty1t',
        width: 100
      },
      {
        title: '坏品',
        dataIndex: 'qty2',
        key: 'qty2',
        width: 100
      },
      {
        title: '其他',
        dataIndex: 'qty3',
        key: 'qty3',
        width: 100
      },
      {
        title: '单位',
        dataIndex: 'unit',
        key: 'unit',
        width: 100
      },
      {
        title: '添加时间',
        dataIndex: 'billdate',
        key: 'billdate'
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno
          });
          // 操作菜单
          const OperateMenu = (
                  <Menu>
                    <Menu.Item>
                      {/* <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,0)}>寄出发票</a> */}

                      {/* <a className={styles.operrateColor} onClick={(ev) => this.showModal(ev,record,0)}>寄出发票</a> */}
                      <a>更多</a>
                    </Menu.Item>
                  </Menu>
          );
          return (
                  <div>
                    <a onClick={(ev) => { this.detailModal(ev, record); }}>详请</a>
                    <Divider type="vertical" />
                    <Dropdown overlay={OperateMenu}>
                    <span className="ant-dropdown-link" href="#">
                      更多 <Icon type="down" />
                    </span>
                  </Dropdown>
                  </div>
          );
        }

      }
    ];
  }


  componentDidMount() {
    this.getAdminInfo();
  }


     //获取管理员信息
  getAdminInfo = ()　=> {
    http({
      method: 'get',
      api: 'getadminbillno',
      params: {
        admin: this.props.currentUser.admin
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.state.reqParams.userno = data[0].billno;
        this.getList();
      } else {
        message.info(msg);
      }
    }).catch(() => {
      // this.setState({ getLoading: false });
    });
  }

    // 设置modalData
    getModalData = (key) => {
      const key1 = key - 1;
      const modalData1 = this.state.RealNameDate[key1];
      this.setState({
        modalData: modalData1
      });
    }

    // 获取审核列表数据
    getList = () => {
      const { currentUser } = this.props;
      const { reqParams } = this.state;
      http({
        method: 'post',
        api: 'getmalldiscount',
        params: reqParams
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            RealNameDate: data.list,
            getLoading: false,
            total: data.total
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


    // modal
    showModal = (ev, record, d) => {
      this.setState({
        modalVisible: true,
        modifyModalData: record
      });
    };

    handleOk = (e) => {
      this.setState({
        status: 2
      }), this.setRealNameList();
    };

    // 取消录入操作
    handleCancel = (e) => {
      this.props.form.resetFields();
      this.setState({
        modalVisible: false,
        modifyModalData: {}
      });
    };

    // 未通过
    notAdopted = (e) => {
      this.setState({
        status: -1
      }), this.setRealNameList();
    }
    // ////////////


    // /未通过原因

    onChange = (value) => {
    }

    onBlur = () => {
    }

    onFocus = () => {
    }

    onSearch = (val) => {
    }
    // /


    // 更新审核列表数据
    setRealNameList = () => {
      if (this.state.status == -10) { return; }
      http({
        method: 'post',
        api: 'set_realname',
        params: {
          userno: this.props.currentUser.userno,
          billno: this.state.modalData['billno'],
          status: this.state.status
        }
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            getLoading: false,
            modalVisible: false,
            status: -10
          }), this.getList();
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


    // ///////////////////////////////////////////////
    // 详情modal
    detailModal =(ev, data) => {
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
      let status = '';
      if (d == 0) {
        status = '未提交';
      } else if (d == 1) {
        status = '已提交(审核中)';
      } else if (d == 2) {
        status = '通过';
      } else if (d == 3) {
        status = '不通过';
      } else if (d == -1) {
        status = '无效';
      }
      return status;
    }

    // 分页
    getPaginationdata = (page, pageSize) => {
      this.state.reqParams.page = page;
      this.getList();
    }

    // 日期选择
    onChangeRangePicker = (dates, dateStrings) =>　{
      this.state.reqParams.begin = dateStrings[0];
      this.state.reqParams.end = dateStrings[1];
      this.getList();
    }

    // 搜索
    onSearch = (value) => {
      this.state.reqParams.searchkey = value;
      this.getList();
    }


    confirm = (ev, record, d) => {
      let calltxt = '';
      switch (d) {
        case 0:
          calltxt = '寄出发票';
          break;
      }
      Modal.confirm({
        title: `发票是否已开状态设置为 ${calltxt}`,
        content: `你确定要把税号为 ${record.dutyno} 的发票是否已开状态状态设置为 “${calltxt}” 吗？`,
        okText: '确认',
        cancelText: '取消',
        onOk: () => this.setDutynoStatus(record, calltxt),
        onCancel: this.confirmHandleCencle
      });
    }


    // 设置跟进状态
    setDutynoStatus = (record, d) => {
      const calltxt = d;
      http({
        method: 'post',
        api: 'setcalltxt',
        params: {
          flag: this.state.reqParams.flag,
          billno: record.billno,
          calltxt
        }
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


    // 提交表单
    handleSubmit = (e) => {
      const { modifyModalData } = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
        if (!err) {
          http({
            method: 'post',
            api: 'postinvoice',
            params: {
              billno: modifyModalData.billno,
              express_name: values.express_name,
              express_no: values.express_no
            }
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


    render() {
      const { getLoading, detailModalData, modifyModalData } = this.state;
      const { Option } = Select;
      const { getFieldDecorator } = this.props.form;
      return (
        <PageHeaderWrapper>
          <div>
            <div className={styles.example}>
              <Spin spinning={getLoading} />
            </div>
            <Card>
              <RangePicker size="large" style={{ margin: '0 0 15px 0' }} onChange={this.onChangeRangePicker} />
              <Divider type="vertical" />
              <Search
                placeholder="请输入商品名称"
                onSearch={this.onSearch}
                enterButton="搜索"
                size="large"
                style={{ width: 320, height: '20px' }}
              />
              <Table
                onRow={(record) => {
                    return {
                      onClick: (event) => {

                        this.setState({
                          modalData: record,
                          RealName_billno: record.billno
                        });
                      } // 点击行
                    };
                  }}
                columns={this.columns}
                dataSource={this.state.RealNameDate}
                scroll={{ x: 2500, y: 500 }}
                pagination={{
                    pageSize: this.state.reqParams.pagesize,
                    total: this.state.total,
                    onChange: this.getPaginationdata
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
                  </Button>
                ]}
              >
                <div className={styles.detailContainer}>
                  <h3 style={{ color: 'red', fontWeight: '700', width: '100%', textAlign: 'center' }}>发票详情</h3>
                  <p><span>税号：</span><span className="content">{modifyModalData.dutyno}</span></p>
                  <p><span>发票是否已开：</span><span>{modifyModalData.fill == 0 ? '未开':'已开'}</span></p>
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
                <div style={{ width: '100%', height: '1px', background: 'red', marginBottom: '20px' }} />
                <h3 style={{ color: 'red', fontWeight: '700', width: '100%', textAlign: 'center' }}>添加物流信息</h3>
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
                  </Button>
                ]}
            >
              <div className={styles.detailContainer}>
                  <p><span>商品图片：</span><span className="content"> <img style={{ width: '180px', height: '180px' }} src={this.state.modalData.image1} alt="img" /></span></p>
                  <p><span>商品名称：</span><span className="content">{this.state.modalData.waresname}</span></p>
                  <p><span>商品billno：</span><span className="content">{this.state.modalData.billno}</span></p>
                  <p><span>类型：</span><span className="content">{this.state.modalData.type == 0 ? '进销的产品':'生产的产品'}</span></p>
                  <p><span>系列：</span><span className="content">{this.state.modalData.series}</span></p>
                  <p><span>型号：</span><span className="content">{this.state.modalData.model}</span></p>
                  <p><span>价格：</span><span className="content">{this.state.modalData.price}</span></p>
                  <p><span>商城在售：</span><span className="content">{this.state.modalData.onsale == 0 ? '下架不在售':'上架在售'}</span></p>
                  <p><span>单位：</span><span className="content">{this.state.modalData.unit}</span></p>
                  <p><span>库存数量：</span><span className="content">{this.state.modalData.qty}</span></p>
                  <p><span>次品：</span><span className="content">{this.state.modalData.qty1}</span></p>
                  <p><span>坏品：</span><span className="content">{this.state.modalData.qty2}</span></p>
                  <p><span>其他：</span><span className="content">{this.state.modalData.qty3}</span></p>
                  <p><span>产品型号：</span><span className="content">{this.state.modalData.model}</span></p>
                  <p><span>产品编码：</span><span className="content">{this.state.modalData.productno}</span></p>
                  <p><span>操作员：</span><span className="content">{this.state.modalData.username}</span></p>
                  <p><span>添加时间：</span><span className="content">{this.state.modalData.billdate}</span></p>
                  <p><span>描述：</span><span className="content">{this.state.modalData.description}</span></p>
                </div>
            </Modal>
          </div>
        </PageHeaderWrapper>
      );
    }
}


export default PreferentialActivity;
