import React, { PureComponent, Fragment } from 'react';
import { Divider, Modal, Form, Col, Row, Spin, Card, Button, Table, message, Input } from 'antd';
import { connect } from 'dva';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
const { Search } = Input;
import styles from './WarehouseGood.less';
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class WarehouseGood extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      getLoading: true,
      reqParams: {
        admin: this.props.currentUser.admin,
        pagesize: 15,
        page: 1,
      },
      listData: {
        list: [],
        total: 0,
      },
      Exchangevisible:false,
      TabVisible:false
    };
    this.columns = [
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        fixed: 'left',
        render: (text, record) => {
          return <img style={{ width: 100, height: 60 }} src={record.image1} />;
        },
      },
      {
        title: '商品名称',
        width: 150,
        dataIndex: 'warename',
        key: 'warename',
      },
      {
        title: '商品编码',
        dataIndex: 'productno',
        key: 'productno',
        width: 120,
      },
      {
        title: '价格',
        dataIndex: 'price',
        key: 'price',
        width: 80,
      },
      {
        title: '总量',
        dataIndex: 'qtykey',
        key: 'qtykey',
        width: 80,
        render: (text, record) => {
          let numberqty;
          numberqty =
            parseInt(record.qty) +
            parseInt(record.qty1) +
            parseInt(record.qty2) +
            parseInt(record.qty3);
          return (
            <div>
              {numberqty <= 100 ? (
                <div style={{ color: 'red' }}>{numberqty}</div>
              ) : (
                <div>{numberqty}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '成品',
        dataIndex: 'qty',
        key: 'qty',
        width: 80,
        render: (text, record) => {
          return (
            <div>
              {record.qty <= 100 ? (
                <div style={{ color: 'red' }}>{record.qty}</div>
              ) : (
                <div>{record.qty}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '次品',
        dataIndex: 'qty1',
        key: 'qty1',
        width: 80,
        render: (text, record) => {
          return (
            <div>
              {record.qty1 >= 100 ? (
                <div style={{ color: 'red' }}>{record.qty1}</div>
              ) : (
                <div>{record.qty1}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '坏品',
        dataIndex: 'qty2',
        key: 'qty2',
        width: 80,
        render: (text, record) => {
          return (
            <div>
              {record.qty2 >= 100 ? (
                <div style={{ color: 'red' }}>{record.qty2}</div>
              ) : (
                <div>{record.qty2}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '其他',
        dataIndex: 'qty3',
        key: 'qty3',
        width: 80,
        render: (text, record) => {
          return (
            <div>
              {record.qty3 >= 100 ? (
                <div style={{ color: 'red' }}>{record.qty3}</div>
              ) : (
                <div>{record.qty3}</div>
              )}
            </div>
          );
        },
      },
      {
        title: '原仓库',
        dataIndex: 'sorcedepot',
        key: 'sorcedepot',
        width: 100,
      },
      {
        title: '目的仓库',
        dataIndex: 'destination',
        key: 'destination',
        width: 100,
      },
      {
        title: '描述',
        dataIndex: 'description',
        key: 'description',
        width: 100,
      },
      {
        title: '单位',
        dataIndex: 'unit',
        key: 'unit',
        width: 100,
      },
      {
        title: '操作日期',
        dataIndex: 'billdate',
        key: 'billdate',
      },
      {
        title: '操作',
        key: 'action',
        fixed: 'right',
        width: 150,
        render: text => (
          <Fragment>
            <a onClick={() => this.gotoEdit(text)}>编辑</a>
            <Divider type="vertical" />
            <a onClick={ev => this.delTrack(ev, text)}>删除</a>
          </Fragment>
        ),
      },
    ];
  }

  componentDidMount() {
    this.getList();
  }
  //去调仓
  gotoAdd=_=>{
    this.setState({
      Exchangevisible:true
    })
  }
  //获取调仓记录
  getList = () => {
    const { reqParams } = this.state;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getrollover',
      params: {
        ...reqParams,
      },
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          console.log(data);
          this.setState({
            listData: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData: {
              list: [1],
            },
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
  onCancel=_=>{
    this.setState({
      Exchangevisible:false,
    })
    // this.form.resetFields()
    this.props.form.resetFields();
  }
  onClick=_=>{
    this.setState({
      TabVisible:true
    })
  }
  TabonCancel=_=>{
    this.setState({
      TabVisible:false
    })
    this.props.form.setFieldsValue({
      waresname:'0000'
    })
  }
  render() {
    const { getLoading, delLoading, listData,Exchangevisible,TabVisible} = this.state;
    const {
      form: { getFieldDecorator,setFieldsValue}
    } = this.props;
    return (
      <PageHeaderWrapper>
        <Button icon="plus" type="primary" onClick={this.gotoAdd}>
          商品调仓
        </Button>
        <Table
          rowKey={record => record.id}
          scroll={{ x: 1700, y: 600 }} //高
          dataSource={listData.list} //数据来源
          columns={this.columns} //每行显示
          pagination={{
            current: this.state.reqParams.page,
            onChange: this.handleTableChange,
            pageSize: 15,
            defaultCurrent: 1,
            total: listData.total,
          }}
        />
        <Modal
        visible={Exchangevisible}
        title={<div style={{textAlign:'center'}}>商品调仓</div>}
        width="40%"
        onCancel={this.onCancel}
        maskClosable={false}
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="名称">
                  {getFieldDecorator('waresname', {
                    rules: [{ required: true, message: '请输入商品名称' }],
                    initialValue: '请选择要调仓的货品',
                  })(<Input placeholder="请输入商品名称" id="waresname" name="waresname" type="text" onClick={this.onClick} />)}
                </Form.Item>
              </Col>
            </Row>
          </Form>
          <Modal
          title={<div style={{textAlign:'center'}}>选择要调仓的货品</div>}
          visible={TabVisible}
          footer={null}
          width='60%'
          onCancel={this.TabonCancel}        
          >
            <Table
              rowKey={record => record.id}
              scroll={{ x: 1700, y: 600 }} //高
              dataSource={listData.list} //数据来源
              columns={this.columns} //每行显示
              pagination={{
                current: this.state.reqParams.page,
                onChange: this.handleTableChange,
                pageSize: 15,
                defaultCurrent: 1,
                total: listData.total,
              }}
            />
          </Modal>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}
export default WarehouseGood;
