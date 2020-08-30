import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';
import {Modal,Form, Spin, Card, Button, Table, message} from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import CrudeAdd from './CrudeAdd';
import styles from './stock.less';
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class StockIn extends PureComponent {
  constructor(props) {
    super(props);
    // 进货总计
    this.columns = [
    {
      title: '总金额',
      dataIndex: 'price',
      width: "30%"
    }, {
      title: '总件数',
      dataIndex: 'qty',
      width: "30%"
    }, {
      title: '进货日期',
      dataIndex: 'billdate',
      width: "30%"
    },
    {/*占位使其不会变型*/},
    {
      title: '操作',
      fixed: 'right',
      width: 80,
      render: text => (
        <Fragment>
          <a onClick={() => this.gotoEdit(text)}>详情</a>
        </Fragment>
      )
    }];
    //进货详情
    this.columns1 = [
      {
        title: '商品图片',
        dataIndex: 'image1',
        width: "10%",
        render: (text, record) => {
          return <img style={{ width: 100, height: 60 }} src={record.image1} />;
        },
      },{
        title: '名称',
        dataIndex: 'warename',
        width: "13%"
      },{
        title: '单价',
        dataIndex: 'price',
        width: "10%"
      },{
        title: '数量',
        dataIndex: 'qty',
        width: "10%"
      },{
        title: '单位',
        dataIndex: 'unit',
        width: "10%"
      },{
        title: '操作员',
        dataIndex: 'username',
        width: "10%"
      },{
        title: '供应商',
        dataIndex: 'customername',
        width: "13%"
      },{
        title: '进货日期',
        dataIndex: 'billdate',
        width: "30%"
      },
      {/*占位使其不会变型*/},
      {
        title: '操作',
        fixed: 'right',
        width: 100,
        render: text => (
          <Fragment>
            <a onClick={() => this.gotoEdit1(text)}>详情</a>
            <a style={{marginLeft:20}} onClick={() => this.goretreat(text)}>返回</a>
          </Fragment>
        )
      }];
    this.state = {
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        admin:props.currentUser.admin,
        page: 1,
        flag: 0,
        customerno:'',
      },
      getLoading: false,
      delLoading: false,
      showTableAdd: false,
      addOrEdit: null,
      auditFlag: null,
      tabstste:'0',
      visibleModal:false,
      modalData:{}
    };

    props.getContext && props.getContext(this);
  }

  componentDidMount() {
    this.getList();
  }

  gotoAdd = () => {
    this.setState({
      showCrudeAdd: true,
      addOrEdit: '0',
      activeItem: {}
    });
  }
  //详情
  gotoEdit =async (item) => {
   await this.setState({
      data:item.billdate,
      tabstste:'1'
    });
    this.getList1();
  }
  gotoEdit1 =async (item) => {
    await  this.setState({
      visibleModal:true,
      modalData:item
    })
   }
  goretreat=async (item) => {
    await this.setState({
      data:item.billdate,
      tabstste:'0'
    });
    this.getList();
  }
  getList = () => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'get_stock_statistic',
      params: {
        ...reqParams
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
            list: [1],
            total: 1
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }
  getList1 = () => {
    const { getLoading,data} = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'get_stock_body',
      params: {
        admin: this.props.currentUser.admin,
        date:data,
        flag:'0',
        customerno:''
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
            list: [1],
            total: 1
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }
  EditHandleCancel=()=>{
    this.setState({
      visibleModal:false
    })
  }
  render() {
    const {visibleModal, modalData,listData, getLoading, delLoading,tabstste } = this.state;
    return (
      <PageHeaderWrapper>
        <div>
          <Spin spinning={getLoading || delLoading}>
            <Card bordered={false}>
              <Button icon="plus" type="primary" onClick={this.gotoAdd} style={{ marginTop: 10 }}>
          新增商品进货信息
              </Button>
              <Table
                rowKey={record=>record.id}
                scroll={{ x: 1250, y: 350 }}
                size="middle"
                columns={tabstste=='0'?this.columns:this.columns1}
                dataSource={listData.list}
                onChange={this.handleTableChange}
              />
            </Card>

          </Spin>
          <CrudeAdd
            bordered={false}
            visible={this.state.showCrudeAdd}
            addOrEdit={this.state.addOrEdit}
            activeItem={this.state.activeItem}
            handleRefresh={() => this.onRefresh('reset')}
            handleVisible={bool => this.setState({ showCrudeAdd: bool })}
          />
        </div>
        {/* 详情查看 */}
        <Modal
         maskClosable={false}
         visible={visibleModal}
         title="订单详情"
         onCancel={this.EditHandleCancel}
         footer={[
          <Button key="back" onClick={this.EditHandleCancel}>
            取消
          </Button>
        ]}
        >
             <div className={styles.detailContainer}>
              <p>
                <span>商品图片：</span>
                <span className="content">
                  {' '}
                  <img
                    style={{ width: '180px', height: '180px' }}
                    src={modalData.image1}
                    alt="img"
                  />
                </span>
              </p> 
              <p>
                <span>商品名称：</span>
                <span className="content">{modalData.warename}</span>
              </p>
              <p>
                <span>型号：</span>
                <span className="content">{modalData.model}</span>
              </p>
              <p>
                <span>价格：</span>
                <span className="content">{modalData.price}</span>
              </p>
              <p>
                <span>商城在售：</span>
                <span className="content">
                  {modalData.onsale == 0 ? '下架不在售' : '上架在售'}
                </span>
              </p>
              <p>
                <span>单位：</span>
                <span className="content">{modalData.unit}</span>
              </p>
              <p>
                <span>库存数量：</span>
                <span className="content">{modalData.qty}</span>
              </p>
              <p>
                <span>次品：</span>
                <span className="content">{modalData.qty1}</span>
              </p>
              <p>
                <span>坏品：</span>
                <span className="content">{modalData.qty2}</span>
              </p>
              <p>
                <span>其他：</span>
                <span className="content">{modalData.qty3}</span>
              </p>
              <p>
                <span>产品型号：</span>
                <span className="content">{modalData.model}</span>
              </p>
              <p>
                <span>产品编码：</span>
                <span className="content">{modalData.productno}</span>
              </p>
              <p>
                <span>操作员：</span>
                <span className="content">{modalData.username}</span>
              </p>
              <p>
                <span>添加时间：</span>
                <span className="content">{modalData.billdate}</span>
              </p>
              <p>
                <span>描述：</span>
                <span className="content">{modalData.description}</span>
              </p>
            </div>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}

export default StockIn;
