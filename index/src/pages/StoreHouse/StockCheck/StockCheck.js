import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import {Form,Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, Row } from 'antd';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './StockCheck.less';

@Form.create()
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
class StockCheck extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: true,
      stockCheckDate: [],
      EditData: {
        modalTitle: '',
        data: {},
        EditModalVisible: true,
      },
      RealName_billno: '',
      modalVisible: false, //modal
      modalData: {},
    };
    this.columns = [
      {
        title: '商品图片',
        width: 110,
        dataIndex: 'warepic',
        key: 'warepic',
        fixed: 'left',
        render: (text, record) => {
          return <img style={{ width: 60, height: 50 }} src={record.warepic} />;
        },
      },
      {
        title: '商品名称',
        width: 170,
        dataIndex: 'warename',
        key: 'warename',
        fixed: 'left',
      },
      {
        title: '商品编号',
        dataIndex: 'wareno',
        key: 'wareno',
        width: 170,
      },
      {
        title: '商品二维码',
        dataIndex: 'productno',
        key: 'productno',
        width: 170,
      },
      {
        title: '盘点人',
        dataIndex: 'username',
        key: 'username',
        width: 110,
      },
      {
        title: '盘点日期',
        dataIndex: 'checkdate',
        key: 'checkdate',
        width: 140,
      },
      {
        title: '原数量',
        dataIndex: 'qty',
        key: 'qty',
        width: 110,
      },
      {
        title: '原次品',
        dataIndex: 'qty1',
        key: 'qty1',
        width: 110,
      },
      {
        title: '原坏品',
        dataIndex: 'qty2',
        key: 'qty2',
        width: 110,
      },
      {
        title: '其他',
        dataIndex: 'qty3',
        key: 'qty3',
        width: 110,
      },
      {
        title: '现数量',
        dataIndex: 'newqty',
        key: 'newqty',
        width: 100,
      },
      {
        title: '现次品',
        dataIndex: 'newqty1',
        key: 'newqty1',
        width: 110,
      },
      {
        title: '现坏品',
        dataIndex: 'newqty2',
        key: 'newqty2',
        width: 110,
      },
      {
        title: '现其他',
        dataIndex: 'newqty3',
        key: 'newqty3',
      },

      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.state.EditData.data = record;
          this.setState({
            RealName_billno: record.billno,
          });
          let OperateMenu = (
            <Menu>
              <Menu.Item>
                <a onClick={() => this.gotoEdit(text)}>
                  编辑
                </a>
              </Menu.Item>
            </Menu>
          );
          return (
            <div>
              <a onClick={this.showModal}>详情</a>
              <Divider type="vertical" />
              <Dropdown overlay={OperateMenu}>
                <span className="ant-dropdown-link">
                  更多 <Icon type="down" />
                </span>
              </Dropdown>
            </div>
          );
        },
      },
    ];
    props.getContext && props.getContext(this);
  }
//渲染前
  componentDidMount() {
    this.getList();
  }

  //获取审核列表数据
  getList = () => {
    http({
      method: 'post',
      api: 'get_stockcheck_list',
      params: {
        admin: this.props.currentUser.admin,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            stockCheckDate: data.list,
            getLoading: false,
          });
        } else {
          message.info(msg);
          this.setState({
            stockCheckDate: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };

  //编辑盘点信息
  gotoEdit = text => {
    this.setState({
      addOrEdit: '1',
      activeItem: item,
      showCrudeAdd: true,
  });
  this.getList()
  };

  //详情
  showModal = () => {
    this.setState({
      modalVisible: true,
    });
  };
//详情确定
  handleOk = e => {
    this.setState({
      status: 2,
    }),
      this.setRealNameList();
  };
//详情关闭
  handleCancel = e => {
    this.setState({
      modalVisible: false,
    });
  };
  ///

  //更新审核列表数据
  setRealNameList = () => {
    if (this.state.status == -10) {
      return;
    }
    http({
      method: 'post',
      api: 'set_realname',
      params: {
        userno: this.props.currentUser.userno,
        billno: this.state.modalData['billno'],
        status: this.state.status,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            getLoading: false,
            modalVisible: false,
            status: -10,
          }),
            this.getList();
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
            status: -10,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false, status: -10 });
      });
  };
  //添加
  clickAddData = e => {

  };

  render() {
    const { getLoading, modalData } = this.state;
    return (
      <PageHeaderWrapper>
        <div>
          <div className={styles.example}>
            <Spin spinning={getLoading} />
          </div>

          <Card>
            <Row>
              <Button type="primary" style={{ width: '100px' }} onClick={this.clickAddData}>
                添加
              </Button>
            </Row>
            <Table
              rowKey={record=>record.id}
              onRow={record => {
                return {
                  onClick: event => {
                    this.setState({
                      modalData: record,
                      RealName_billno: record.billno,
                    });
                  }, // 点击行
                };
              }}
              columns={this.columns}
              dataSource={this.state.stockCheckDate}
              scroll={{ x: 1900, y: 400 }}
            />
          </Card>
          {/* 盘点详情 */}
          <div>
            <Modal
              title="盘点详情"
              visible={this.state.modalVisible}
              onOk={this.handleOk}
              onCancel={this.handleCancel}
              maskClosable={false}
              footer={[
                <Button type="primary" key="back" onClick={this.handleCancel}>
                  关闭
                </Button>,
              ]}
            >
              <div className={styles.detailContainer}>
                <p>
                  <span>商品图片：</span>
                  <span>
                    {' '}
                    <img
                      style={{ width: '180px', height: '180px' }}
                      src={modalData.warepic}
                      alt="img"
                    />
                  </span>
                </p>
                <p>
                  <span>商品名称：</span>
                  <span>{modalData.warename}</span>
                </p>
                <p>
                  <span>商品编号：</span>
                  <span>{modalData.wareno}</span>
                </p>
                <p>
                  <span>商品二维码：</span>
                  <span>{modalData.productno}</span>
                </p>
                <p>
                  <span>盘点人：</span>
                  <span>{modalData.username}</span>
                </p>
                <p>
                  <span>盘点日期：</span>
                  <span>{modalData.checkdate}</span>
                </p>
                <p>
                  <span>原数量：</span>
                  <span>{modalData.qty}</span>
                </p>
                <p>
                  <span>原次品：</span>
                  <span>{modalData.qty1}</span>
                </p>
                <p>
                  <span>原坏品：</span>
                  <span>{modalData.qty2}</span>
                </p>
                <p>
                  <span>原其它：</span>
                  <span>{modalData.qty3}</span>
                </p>
                <p>
                  <span>现数量：</span>
                  <span>{modalData.newqty}</span>
                </p>
                <p>
                  <span>现次品：</span>
                  <span>{modalData.newqty1}</span>
                </p>
                <p>
                  <span>现坏品：</span>
                  <span>{modalData.newqty2}</span>
                </p>
                <p>
                  <span>现其它：</span>
                  <span>{modalData.newqty3}</span>
                </p>
                <p>
                  <span>备注：</span>
                  <span>{modalData.remark}</span>
                </p>
              </div>
            </Modal>
          </div>
        </div>
      </PageHeaderWrapper>
    );
  }
}

export default StockCheck;
