import React from 'react';
import { connect } from 'dva';
import { Card, Pagination } from 'antd';
import http from '@/utils/http';
import BillList from '../BillList';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class AwaitingShipment extends React.PureComponent {
  constructor(props){
    super(props);
    this.state = {
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        userno: '',
        flag: 3,
        pageSize: 15,
        page: 1,
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
    // this.getList();
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
    api: 'get_orderin_list',
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


//分页
getPaginationdata=(page, pageSize)=>{
  this.state.reqParams.page = page;
  this.getList();
}

render() {
  return (
    <div>
      <Card style={{border:0}}>
        <BillList dataPlaceholder="ListSource" data={this.state.listData.list} />
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

export default AwaitingShipment;
