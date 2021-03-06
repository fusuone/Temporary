import React, { PureComponent, Fragment } from 'react';
import { Divider, Dropdown,Modal, Menu, Form, Icon, Spin, Card, Button, Table, message ,Input} from 'antd';
import { connect } from 'dva';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import styles from './CommodityShelves.less';
import CommodityShelvesAdd from './CommodityShelvesAdd';
import CommodityShelvesUpdate from'./CommodityShelvesUpdate'
const { Search } = Input;
function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
//表单项变动时改变
@Form.create()
class CommodityShelves extends PureComponent {
  constructor(props){
    super(props);
    this.state = {
      getLoading: true,//加载动画
    }
    //基础商品列表显示
    this.columns1 = [
      {
        title: '图片',
        width: 180,
        dataIndex: 'image1',
        key: 'image1',
        fixed: 'left',
        render: (text, record) => {
          return(
            <img style={{width:100,height:60}} src={record.image1} />
          );
        }
      },
        {
          title: '商品名称',
          dataIndex: 'waresname',
          key: 'waresname',
          width: 180,
        },
        {
          title: '商品编码',
          dataIndex: 'productno',
          key: 'productno',
          width: 150,
        },
        {
          title: '型号',
          dataIndex: 'model',
          key: 'model',
          width: 150,
        },
        {
          title: '系列',
          dataIndex: 'series',
          key: 'series',
          width:150,
        },
        {
          title: '品牌',
          dataIndex: 'brand',
          key: 'brand',
          width: 120,
        },
        {
          title: '分类',
          dataIndex: 'catname',
          key: 'catname',
          width: 120,
        },
        {
          title: '单价',
          dataIndex: 'price',
          key: 'price',
          width: 120,
        },
        {
          title: '单位',
          dataIndex: 'unit',
          key: 'unit',
          width: 100,
        },
        {
          title: '添加时间',
          dataIndex: 'billdate',
          key: 'billdate',
        }, {
          title: '操作',
          key: 'action',
          fixed: 'right',
          width: 150, 
          render: text => (
            <Fragment>
              <a onClick={() => this.gotoEdit(text)}>上架</a>
              <Divider type="vertical" />
            </Fragment>
          )
        }
      ];
      //拓展商品别表
        this.columns = [
          {
            title: '首页图片',
            width: 180,
            dataIndex: 'image1',
            key: 'image1',
            fixed: 'left',
            render: (text, record) => {
              return(
                <img style={{width:100,height:60}} src={record.image1} />
              );
            }
          },
            {
              title: '商品名称',
              dataIndex: 'waresname',
              key: 'waresname',
              width: 180,
            },
            {
              title: '商品编码',
              dataIndex: 'productno',
              key: 'productno',
              width: 150,
            },
            {
              title: '数量',
              dataIndex: 'qty',
              key: 'qty',
              width: 70,
            },
            {
              title: '操作员',
              dataIndex: 'username',
              key: 'username',
              width:120,
            },
            {
              title: '状态',
              dataIndex: 'onsale',
              key: 'onsale',
              width:120,
              render: (text, record) => {
                return(
                  record.onsale1==0?<div style={{color:'red'}}>下架</div>:<div>上架</div>
                );
              }

            },
            {
              title: '型号',
              dataIndex: 'model',
              key: 'model',
              width: 100,
            },
            {
              title: '单价',
              dataIndex: 'price',
              key: 'price',
              width: 120,
            },
            {
              title: '产地',
              dataIndex: 'place',
              key: 'place',
              width: 120,
            },
            {
              title: '单位',
              dataIndex: 'unit',
              key: 'unit',
              width: 120,
            },
            {
              title: '生产日期',
              dataIndex: 'makedate',
              key: 'makedate',
              width:130
            },
            {
              title: '质保期',
              dataIndex: 'warranty',
              key: 'warranty',
              width: 80,
            },
            { title: '限购',
              dataIndex: 'buylimit',
              key: 'buylimit',
              width: 80,
            },
            { title: '净含量',
            dataIndex: 'net',
            key: 'net',
            width: 100,
            },
            {
              title: '描述',
              dataIndex: 'description',
              key: 'description',
            },
            {
            title: '操作',
            key: 'action',
            fixed: 'right',
            width: 150,
            render: text => (
              <Fragment>
                <a onClick={() => this.gotoEdit1(text)}>编辑</a>
                <Divider type="vertical" />
                <Dropdown
                  overlay={
                    <Menu>
                      <Menu.Item>
                        <a onClick={(ev) => this.delTrack(ev,text)}>删除</a>
                      </Menu.Item>
                    </Menu>
                  }
                >
                  <a>更多 <Icon type="down" /></a>
                </Dropdown>
              </Fragment>
            )
          }];
          this.state={
            getLoading: false,//加载动画
            delLoading: false,//删除中
            Tabvisible:true,
            listData: {
              list: [],
              total: 0
            },
            listData1: {
              list: [],
              total: 0
            },
            reqParams: {
              admin: this.props.currentUser.admin, //管理员billno
              keyword:'',
              pagesize: 15,
              page: 1,
            },
          };
    props.getContext && props.getContext(this);
  }
  //生命周期函数，渲染前
  componentDidMount() {
    this.getList();
  }
  //上架
  gotoEdit =(item)=>{
    this.setState({
      activeItem: item,
      showCommodityShelvesAdd:true
    })
  }
  //编辑
  gotoEdit1 =(item)=>{
    this.setState({
      activeItem: item,
      showCommodityShelvesUpdate:true
    })
  }
  //删除
  delTrack =(ev,item)=>{
    Modal.confirm({
      title: `商品信息删除`,
      content: `你确定要把商品billno为 ${item.billno} 的商品执行删除操作吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.confirmHandleOk(item.billno),
      onCancel: this.confirmHandleCencle,
    });
  }
  //确认删除触发
  confirmHandleOk = (d) => {
    http({
      method: 'get',
      api: 'delwares',
      params: {
        items: d,
        flag:1
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        message.info("删除成功");
        this.getList();
      } else {
        message.warn(msg);
      }
    }).catch(() => {
      message.info("操作失败");
    });
  }
    //取消删除触发
  confirmHandleCencle = () => {
    message.info("操作已取消");
  }
  //分页
  handleTableChange = (page,pageSize)=>{
    this.state.reqParams.page = page;
    this.getList()
    this.getList1()
  }
  //点击添加触发
  gotoAdd = ()=>{
    this.state.Tabvisible=false;
    this.getList1();
  }
  //回退
  goBack=()=>{
    this.state.Tabvisible=true;
    this.getList();
  }
  getList =() => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getmallwares',
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
            list: [],
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }
   //搜索商品
   onSearch = (value) => {
     this.state.reqParams.keyword = value;
     this.state.Tabvisible==true?this.getList():this.getList1();
    
  }
  changvlse = ()=>{
    const sousuokuang=document.getElementById('sousuokuang').value;
    if(sousuokuang==""){
      this.onSearch()
    }
  }
  getList1 = () => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getwares',
      params: {
        ...reqParams
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          listData1: {
            list: data.list,
            total: Number(data.total)
          },
          getLoading: false
        });
      } else {
        message.warn(msg);
        this.setState({
          listData1: {
            list: [],
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }
  render() {
    const { listData, getLoading, delLoading,listData1} = this.state;
    return (
      <PageHeaderWrapper>
        <div>
          <Spin spinning={getLoading || delLoading}>
            <Card bordered={false}>
              <Button icon="plus" 
              type="primary" 
              onClick={this.gotoAdd}
              style={this.state.Tabvisible==true?{ marginTop: 10}:{display:"none"}}>
               商品上架
              </Button>
              <Button icon="left" 
              type="primary" 
              onClick={this.goBack}
              style={this.state.Tabvisible==false?{ marginTop: 10}:{zIndex:"-2000"}}>
               已上架商品
              </Button>
              <Search
                  id="sousuokuang"
                  placeholder="请输入商品名称或商品编码" 
                  onSearch={this.onSearch} 
                  enterButton="搜索"
                  onChange={this.changvlse}
                  size="large"
                  style= {
                    this.state.Tabvisible==true?{
                      width:"60%" ,
                      textAlign:"left",
                      marginLeft:"10%"
                    }:{
                      marginLeft:"200px",
                      width:"60%",
                      textAlign:"left",
                      height: "20px",
                      marginBottom:"30px"
                    }}
              />
              <Table 
                rowKey={record=>record.id}
                className={styles['ant-table']}
                scroll={{ x:1900,y:800 }}//高
                dataSource={this.state.Tabvisible==true ?listData.list:listData1.list}//数据来源
                columns={this.state.Tabvisible==true ?this.columns:this.columns1}//每行显示
                pagination={{
                  current: this.state.reqParams.page,
                  onChange:this.handleTableChange,
                  pageSize: 15,
                  defaultCurrent: 1,//默认的当前页数
                  total: this.state.Tabvisible==true ? listData.total:listData1.total,
                }}
              />
            </Card>
          </Spin>
          <CommodityShelvesAdd
            visible={this.state.showCommodityShelvesAdd}//是否显示添加页面
            activeItem={this.state.activeItem}//把参数传递到子页面
            handleRefresh={() =>this.getList()}//回调刷新页面
            Tabvisible={bools =>this.setState({Tabvisible:bools})}//回调切换显示的表单
            handleVisible={bool => this.setState({ showCommodityShelvesAdd: bool })}//设置控制显示隐藏的状态(由子页面回调)
          />
          <CommodityShelvesUpdate
            visible={this.state.showCommodityShelvesUpdate}//是否显示添加页面
            activeItem={this.state.activeItem}//把参数传递到子页面
            handleRefresh={() =>this.getList()}//回调刷新页面
            handleVisible={bool => this.setState({ showCommodityShelvesUpdate: bool })}//设置控制显示隐藏的状态(由子页面回调)
          />
        </div>
      </PageHeaderWrapper>
    );
  }
}
  export default CommodityShelves;