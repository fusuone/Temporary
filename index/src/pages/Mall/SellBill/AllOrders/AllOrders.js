import React, { PureComponent } from 'react';
import { connect } from 'dva';
import ExportJsonExcel from 'js-export-excel';
import { Card, Select, Form, Input, DatePicker, Button, message, Pagination } from 'antd';
import http from '@/utils/http';
import BillList from '../BillList';
import styles from './AllOrders.less';


const Search = Input.Search;
const { MonthPicker, RangePicker, WeekPicker } = DatePicker;

// //
const Option = Select.Option;

function handleChange(value) {
}


//搜索订单号
function searchOrder(){
    alert('ddddd');
}
  
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class AllOrders extends PureComponent{
  constructor(props){
    super(props);
    this.state = {
      listData: {
        list: [],
        total: 0,
      },
      reqParams: {
        userno: '',
        flag: 99,
        pageSize: 15,
        page: 1,
        
      // ...initialSearchParams
      },
      paginationdata: {
        list: [],
        current: 1,
        pageSize: 5,
      },
      
      selectedRowKeys: [],
      getLoading: false,
      delLoading: false,
      showTableAdd: false,
      addOrEdit: null,
      auditFlag: null
    };
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
         this.state.reqParams.userno=data[0].billno;
         this.getList();
        
      } else {
        message.warn(msg);
      }
    }).catch(() => {
      // this.setState({ getLoading: false });
    });
  }
  
// 
getList = () => {
  const { getLoading, reqParams } = this.state;
  this.setState({ getLoading: true });
  http({
    method: 'get',
    api: 'get_orderout_list',
    params: {
      ...reqParams
    }
  }).then((result) => {
    const { status, msg, data } = result;
    let dataList;
    if (status === '0') {
      dataList = data.list;
      dataList.map((value,key)=>{
        http({
            method: 'get',
            api: 'orderdetail',
            params: {
              orderno: value["billno"]
            }
          }).then((result) => {
            const { status, msg, data } = result;
            if (status === '0') {
              dataList[key]["wareimage"] =  data[0]["wareimage"];
              dataList[key]["warename"] =  data[0]["warename"];
            } else {
              message.warn(msg);
            }
          }).catch(() => {
            this.setState({ getLoading: false });
          });
        }),
        this.setState({
        listData: {
            list: dataList,
            total: Number(data.total)
          },
          getLoading: false
        });
    } else {
      message.warn(msg);
      this.setState({
        getLoading: false
      });
    }
  }).catch(() => {
    this.setState({ getLoading: false });
  });
}



    // // 订单选择
    // billSelection=(value)=>{
    //   let selectKeyValue = value.key;
    //   switch(selectKeyValue){
    //     case "0":
    //       this.state.reqParams.billtype='';
    //       this.getList();
    //       message.info('全部订单');
    //       break;
    //     case "1":
    //       this.state.reqParams.billtype='普通订单';
    //       this.getList();
    //       message.info('普通订单');
    //       break;
    //     case "2":
    //       this.state.reqParams.billtype='拼团订单';
    //       this.getList();
    //       message.info('拼团订单');
    //       break;
    //     case "3":
    //       this.state.reqParams.billtype='抽奖订单';
    //       this.getList();
    //       message.info('抽奖订单');
    //       break;
    //   }
    // }

    // 状态选择
    stateSelection=(value)=>{
      let stateSelectValue = value.key;
      switch(stateSelectValue){
        case "0":
          this.state.reqParams.flag=99;
          this.getList();
          message.info('全部状态');
          break;
        case "1":
          this.state.reqParams.flag=0;
          this.getList();
          message.info('待付款');
          break;
        case "2":
          this.state.reqParams.flag=2;
          this.getList();
          message.info('已付款/待发货');
          break;
        case "3":
          this.state.reqParams.flag=3;
          this.getList();
          message.info('待收货');
          break;
        case "4":
          this.state.reqParams.flag=4;
          this.getList();
          message.info('用户取消');
          break;
        case "5":
          this.state.reqParams.flag=5;
          this.getList();
          message.info('待付款超时');
          break;
        case "6":
          this.state.reqParams.flag=6;
          this.getList();
          message.info('待评价/确认收货');
          break;
      }
    }
  //订单日期搜素
  searchBillDate=(date,dateString) => {
    this.state.reqParams.begindate=dateString[0];
    this.state.reqParams.enddate=dateString[1];
    this.getList();
  }

  //获取订单状态
  judgeBillState = (value) =>{
    if(value==0){
      return "待付款";
    }else if(value==2){
      return "待发货";
    }else if(value==3){
      return "已付款/待收货";
    }else if(value==4){
      return "用户取消";
    }else if(value==5){
      return "待付款超时";
    }else if(value==6){
      return "待评价/确认收货";
    }else if(value == -1){
      return "删除";
    }
  }


  // 订单搜索
  searchBill = (value) =>{
    this.state.reqParams.searchValue = value;
    this.getList();
    this.state.reqParams.searchValue = "";
  }
  // searchOrder=(value)=>{
  //   alert(`${value}`);
  // }

  //分页
  getPaginationdata=(page, pageSize)=>{
    this.state.reqParams.page = page;
    this.getList();
  }


//导出全部
downloadExcelALLBillData = () => {
  http({
    method: 'get',
    api: 'get_orderout_list',
    params: {
      userno: this.state.reqParams.userno,
      flag: this.state.reqParams.flag,
      ispaging: 2,
    }
  }).then((result) => {
    const { status, msg, data } = result;
    let dataList;
    if (status === '0') {
      dataList = data.list;
      dataList.map((value,key)=>{
        http({
            method: 'get',
            api: 'orderdetail',
            params: {
              orderno: value["billno"]
            }
          }).then((result) => {
            const { status, msg, data } = result;
            if (status === '0') {
              dataList[key]["wareimage"] =  data[0]["wareimage"];
              dataList[key]["warename"] =  data[0]["warename"]; 
            } else {
              // message.warn("msg);
            }
          }).catch(() => {
            // message.warn("");
          });
        }),
        this.downloadExcel(dataList);
    } else {
      message.warn("从服务器获取数据失败不能导出数据，"+msg);
    }
  }).catch(() => {
    message.warn("数据导出数据失败");
  });
}

//导出本页
downloadExcelBillData = () => {
  let data = this.state.listData.list ? this.state.listData.list : '';//表格数据
  this.downloadExcel(data);
}


//导出订单数据为excel格式文件
downloadExcel = (data) => {
    var option={};
    let dataTable = [];
    if (data) {
      for (let i in data) {
        if(data){
          let obj = {
            '订单号': data[i].billno,
            '订单时间': data[i].billdate,
            '商品名称': data[i].warename,
            '商品图片': data[i].wareimage,
            '数量': data[i].qty,
            '合计': data[i].amount,
            '购买人名称': data[i].buyername,
            '买家头像': data[i].buyeravatar,
            '收货人': data[i].c_linkman,
            '收货人手机/电话': data[i].c_tel,
            '收货地址': data[i].c_address,
            '快递单号': data[i].express_no,
            '快递公司': data[i].express_company,
            '支付方式': data[i].payway,
            '付款时间': data[i].paydate,
            '店铺名称': data[i].salername,
            '店铺logo': data[i].salerlogo,
            '处理完成日期': data[i].enddate,
            '订单状态':  this.judgeBillState(data[i].billstate),
          }
          dataTable.push(obj);
        }
      }
    }
    option.fileName = '买入订单数据';
    option.datas=[
      {
        sheetData:dataTable,
        sheetName:'买入订单数据',
        sheetFilter:['订单号','订单时间','商品名称','商品图片','数量','合计','购买人名称','买家头像','收货人','收货人手机/电话','收货地址','快递单号','快递公司','支付方式','付款时间','店铺名称','店铺logo','处理完成日期','订单状态'],
        sheetHeader:['订单号','订单时间','商品名称','商品图片','数量','合计','购买人名称','买家头像','收货人','收货人手机/电话','收货地址','快递单号','快递公司','支付方式','付款时间','店铺名称','店铺logo','处理完成日期','订单状态'],
      }
    ];
    
    var toExcel = new ExportJsonExcel(option); 
    toExcel.saveExcel();        
  }


   
  render(){
    return(
      <div>
        {/* <Card style={{textAlign:"center",border:0}}>
          <Search
              ref="search1"
              style={{ width: 270,marginRight: 10 } }
              placeholder="订单编号或收人姓名"
              onSearch={this.searchBill}
              // onPressEnter={ this.searchOrder }
              enterButton
          />
        </Card> */}
        <Card style={{border:0,marginTop:0}}>
          <Form>
              <Form.Item>
                  {/* <Select
                      labelInValue defaultValue={{ key: '全部订单' }}
                      showSearch
                      style={{ width: 150,marginRight: 10 }}
                      optionFilterProp="children"
                      onSelect={this.billSelection}
                      filterOption={(input, option) => option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0}
                  >
                      <Option value="0">全都订单</Option>
                      <Option value="1">普通订单</Option>
                      <Option value="2">拼团订单</Option>
                      <Option value="3">抽奖订单</Option>
                  </Select> */}
                  <Select
                      labelInValue defaultValue={{ key: '全部状态' }}
                      showSearch
                      style={{ width: 150, marginRight: 10 }}
                      optionFilterProp="children"
                      onSelect={this.stateSelection}
                      filterOption={(input, option) => option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0}
                  >
                    {/* -1 删除 0 待付款 2 已付款/待发货 3 待收货 4 用户取消 5 待付款超时 6 待评价/确认收货 */}
                      <Option value="0">全部状态</Option>
                      <Option value="1">待付款</Option>
                      <Option value="2">已付款/待发货</Option>
                      <Option value="3">待收货</Option>
                      <Option value="4">用户取消</Option>
                      <Option value="5">待付款超时</Option>
                      <Option value="6">待评价/确认收货</Option>
                  </Select>
                  {/* <Select
                      labelInValue defaultValue={{ key: '请选择品牌' }}
                      showSearch
                      style={{ width: 150, marginRight:10 }}
                      optionFilterProp="children"
                      onSelect={this.brandSelection}
                      filterOption={(input, option) => option.props.children.toLowerCase().indexOf(input.toLowerCase()) >= 0}
                  >
                      <Option value="0">全部</Option>
                      <Option value="1">华为</Option>
                      <Option value="2">实验书店</Option>
                      <Option value="3">小米</Option>
                  </Select> */}
                    {/* <RangePicker
                      style={{ width: 250,marginRight:10} } 
                      onChange={this.searchBillDate} 
                    /> */}
                    <Button style={{marginRight: 10}} onClick={this.downloadExcelBillData} >导出本页</Button>
                    <Button onClick={this.downloadExcelALLBillData} >导出全部</Button> 
              </Form.Item>
          </Form>
            <BillList ref='pageination' dataPlaceholder="ListSource" 
            parentGetList={this.getList}
            data={this.state.listData.list}
              />
        </Card>
        <Card style={{border:0,textAlign:"center"}}>
        <Pagination 
          defaultCurrent={1} 
          current={this.state.reqParams.page}
          pageSize={15}
          total={this.state.listData.total==0?1:this.state.listData.total} 
          disabled={this.state.listData.total==0?true:false} 
          onChange={this.getPaginationdata}
        />
      </Card>
      </div>
    );
  }
}

export default AllOrders;
