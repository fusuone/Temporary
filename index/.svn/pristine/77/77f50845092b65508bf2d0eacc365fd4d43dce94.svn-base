import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select } from 'antd';
import { connect } from 'dva';
import http from '@/utils/http';
import styles from './StockReturn.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';


@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class StockReturn extends PureComponent{
    constructor(props){
        super(props);
        // console.log(this.props.currentUser);
        this.state={
            getLoading: true,
            stockCheckDate: [],
            EditData: {
                data: {},
                EditModalVisible: true,
            },
            RealName_billno: "",
            modalVisible: false, //modal
            status: -10,
          modalData: {}
        }
        this.columns = [
            {
              title: '商品图片',
              width: 110,
              dataIndex: 'warepic',
              key: 'warepic',
              fixed: 'left',
              render: (text, record) => {
                return(
                  <img style={{width:60,height:50}} src={record.warepic} />
                );
              }
            },
            {
              title: '商品名称',
              width: 100,
              dataIndex: 'warename',
              key: 'warename',
              fixed: 'left', 
            },
            {
              title: '商品编号',
              dataIndex: 'wareno',
              key: 'wareno',
              width: 150,
            },
            {
              title: '订单号',
              dataIndex: 'billno',
              key: 'billno',
              width: 150,
            },
            {
              title: '订单时间',
              dataIndex: 'billdate',
              key: 'billdate',
              width: 200,
            },
            {
              title: '退货日期',
              dataIndex: 'backdate',
              key: 'backdate',
              width: 150,
            },
            // {
            //   title: '用户号',
            //   dataIndex: 'userno',
            //   key: 'userno',
            //   width: 100,
            // },
            {
              title: '操作员名',
              dataIndex: 'username',
              key: 'username',
              width: 100,
            },
            {
              title: '退货数量',
              dataIndex: 'qty',
              key: 'qty',
              width: 120,
              render: (text, record) => {
                return(
                  <div>
                    {record.qty}{record.unit}
                  </div>
                );
              }
            },
            // {
            //   title: '产品系列',
            //   dataIndex: 'series',
            //   key: 'series',
            //   width: 100,
            // },
            // {
            //     title: '产品型号',
            //     dataIndex: 'model',
            //     key: 'model',
            //     width: 100,
            //   },
              {
                title: '退货类型',
                dataIndex: 'backtype',
                key: 'backtype',
                width: 100,
                render: (text, record) => {
                  return(
                    <div>
                      {record.backtype==0?"销售退货":"采购退货"}
                    </div>
                  );
                }
              },
              {
                title: '退货金额',
                dataIndex: 'amount',
                key: 'amount',
              },
            {
              title: '操作',
              key: 'operation',
              fixed: 'right',
              width: 130,
              render: (text, record) =>{
                this.state.EditData.data=record;
                this.setState({
                  RealName_billno: record.billno
                });
                let OperateMenu = (
                    <Menu>
                      {/* <Menu.Item>
                        <a onClick={() => {
                              this.setState({
                                  EditData: {
                                      data: record,
                                      EditModalVisible: true
                                  }
                              });
                          }}>编辑
                        </a>
                        <a>删除</a>
                      </Menu.Item> */}
                      <Menu.Item>
                        更多
                      </Menu.Item>
                    </Menu>
                  );
                return(
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
              }
                
            },
          ];

        //   //操作菜单
        //   this.OperateMenu = (
        //     <Menu>
        //       <Menu.Item>
        //         <a >编辑</a>
        //         <a>删除</a>
        //       </Menu.Item>
        //     </Menu>
        //   );
    }


    componentDidMount() {
      this.getList();
    };

    //设置modalData
    getModalData = (key) =>{
      let key1 = key - 1;
      let modalData1 = this.state.stockCheckDate[key1]
      this.setState({
        modalData: modalData1
      });
    }

    //获取退货列表数据
    getList = () => {
      http({
        method: 'post',
        api: 'get_saleback_list',
        params: {
          admin: this.props.currentUser.admin
        }
      }).then((result) => {
        const { status, msg, data } = result;
        // console.log(data);
        if (status === '0') {
          this.setState({
            stockCheckDate: data.list,
            getLoading: false
          });
        } else {
          message.warn(msg);
          this.setState({
            stockCheckDate: [],
            getLoading: false
          });
        }
      }).catch(() => {
        this.setState({ getLoading: false });
      });
    }

    //编辑盘点信息
    gotoEdit = (text) =>{
      console.log(text);
        this.setState({
            EditData: {
                data: text,
                EditModalVisible: true
            }
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





    render(){
      const { getLoading, modalData } = this.state;
      const { Option } = Select;
          
        return(
            <PageHeaderWrapper>
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
                    columns={this.columns} dataSource={this.state.stockCheckDate} scroll={{ x: 1500, y: 300 }} />
                </Card>
                {/* modal */}
                <div>
                    <Modal
                    title="盘点详情"
                    visible={this.state.modalVisible}
                    onOk={this.handleOk}
                    onCancel={this.handleCancel}
                    // width={1000}
                    maskClosable={false}
                    footer={[
                        <Button type="primary" key="back" onClick={this.handleCancel}>
                        关闭
                        </Button>,
                        // <Button type="danger" onClick={this.notAdopted}>
                        // 未通过
                        // </Button>,
                        // <Button key="submit" type="primary" onClick={this.handleOk}>
                        // 通过
                        // </Button>,
                    ]}
                    >
                     <div className={styles.detailContainer}>
                        <p><span>商品图片：</span><span> <img style={{width:"180px",height:"180px"}} src={modalData.warepic} alt="img" /></span></p>
                        <p><span>商品名称：</span><span>{modalData.warename}</span></p>
                        <p><span>商品编号：</span><span>{modalData.wareno}</span></p>
                        <p><span>订单号：</span><span>{modalData.billno}</span></p>
                        <p><span>订单日期：</span><span>{modalData.billdate}</span></p>
                        <p><span>退货日期：</span><span>{modalData.backdate}</span></p>
                        <p><span>操作员编号：</span><span>{modalData.userno}</span></p>
                        <p><span>操作员名称：</span><span>{modalData.username}</span></p>
                        <p><span>客户编号：</span><span>{modalData.customerno}</span></p>
                        <p><span>客户名称：</span><span>{modalData.customername}</span></p>
                        <p><span>退货数量：</span><span>{modalData.qty}</span></p>
                        <p><span>数量单位：</span><span>{modalData.unit}</span></p>
                        <p><span>产品系列：</span><span>{modalData.series}</span></p>
                        <p><span>产品型号：</span><span>{modalData.model}</span></p>
                        <p><span>退货类型：</span><span>{modalData.backtype?"销售退货":"采购退货"}</span></p>
                        <p><span>备注：</span><span>{modalData.remark}</span></p>
                      </div>
                    </Modal>
                </div>
                </div>
            </PageHeaderWrapper>
            
        );
    }
}

export default StockReturn;
