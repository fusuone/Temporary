import React from 'react';
import { connect } from 'dva';
import { Card, List, Avatar, Popconfirm, message, Pagination, Button, Modal, Divider, Dropdown, Icon, Menu } from 'antd'
import http from '@/utils/http';
import style from './BillList.less';



@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))

class AllOrders extends React.PureComponent {
  constructor(props){
    super(props)
    this.state={
      reqParams: {
        billid: '',
        admin: props.currentUser.userno
      // ...initialSearchParams
      },
      getLoading: false,
      billDetailData: []

    }
  }


deleteList = (ev, id) => {
  const { getLoading, reqParams } = this.state;
  this.state.reqParams.billid = id;;
  let listId = id;

  http({
    method: 'get',
    api: 'deletebill',
    params: {
      ...reqParams
    }
  }).then((result) => {
    const { status, msg, data } = result;
    if (status === '0') {
      this.setState({
        getLoading: false
      });
      this.props.parentGetList();
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
cancel = (e) => {
  message.error('已取消删除');
}

//
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
  }else{
    return " ";
  }
}


getOrderDetailButton = (ev, id) =>{
  const { getLoading, reqParams } = this.state;
  this.showModal();
  let item = id;
  this.setState({
    billDetailData: item
  });
}

// 得到订单详情
getOrderDetail = (billno) =>{
  http({
    method: 'get',
    api: 'orderdetail',
    params: {
      orderno: billno
    }
  }).then((result) => {
    const { status, msg, data } = result;
    if (status === '0') {
      this.setState({
        getLoading: false
      });
      return data;
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

getBillGoodsImage = (billno) =>{
  http({
    method: 'get',
    api: 'orderdetail',
    params: {
      orderno: billno
    }
  }).then((result) => {
    const { status, msg, data } = result;
    if (status === '0') {
      message.warn( data[0]["wareimage"]);
      return <Avatar className={style.ml5} size={60} shape="square" src={data[0]["wareimage"]} />
    } else {
      message.warn(msg);
    }
  }).catch(() => {
    this.setState({ getLoading: false });
  });
}

//modal
showModal = () => {
  this.setState({
    visible: true,
  });
};

handleOk = e => {
  this.setState({
    visible: false,
  });
};

handleCancel = e => {
  this.setState({
    visible: false,
  });
};

//更多菜单
getMenu = () =>{
  let menu=(
    <Menu>
      <Menu.Item>
        <a rel="noopener noreferrer">
          更多
        </a>
      </Menu.Item>
    </Menu>
  );
  return menu;
}


  render() {
    return (
      <div>
        {/* <Card> */}
          <List
            className={style.paddingNo}
            itemLayout="horizontal"
            dataSource={this.props.data}
            renderItem={item => (
              <div className={style.BillListItem}>
                <div className={style.billheader}><span>{item.billdate}</span><span>订单号：{item.billno}</span>
              </div>
              <List.Item>
                <List.Item.Meta
                  avatar={<Avatar className={style.ml5} size={60} shape="square" src={item.wareimage? item.wareimage:item.salerlogo} />}
                  title={<div className={style.ItemTitle}><a href={item.link}>{item.title}</a></div>}
                  description=""
                  style={{width:70}}
                />
                 <List.Item.Meta 
                  title={<List className={style.ml5}>商品名称</List>}
                  description={<List className={style.ml5} style={{width:50}}>{item.warename?item.warename:item.salername}</List>}
                />
                <List.Item.Meta 
                  title={<List className={style.ml5}>收货人</List>}
                  description={<List className={style.ml5} style={{width:50}}>{item.c_linkman}</List>}
                />
                <List.Item.Meta 
                  title={<List className={style.ml5}>金额</List>}
                  description={<List className={style.ml5} style={{width:50}}>{item.amount}</List>}
                />
                <List.Item.Meta 
                  title={"状态"}
                  description={<List style={{width:50}}>{this.judgeBillState(item.billstate)}</List>}
                />
                <List.Item.Meta 
                   description={
                    <div style={{width:90}}>
                      <a id={item.billno} onClick={(ev) => {this.getOrderDetailButton(ev, item)}}>详情</a>
                      <Divider type="vertical" />
                      <Dropdown overlay={this.getMenu()}>
                        <span style={{color:"#1890ff"}} className="ant-dropdown-link" href="#">
                          更多 <Icon type="down" />
                        </span>
                      </Dropdown>
                        {/* <Popconfirm title="你确定要删除吗?" id={item.billno} onConfirm={(ev) => {this.deleteList(ev, item.id)}} onCancel={this.cancel} okText="确定" cancelText="取消">
                          <a href="#">删除</a>
                        </Popconfirm> */}
                    </div>}
                />
              </List.Item>
              
              </div>
            )}
          />
          <div>
          {/* <Button type="primary" onClick={this.showModal}>
            Open Modal
          </Button> */}
          <Modal
            title="订单详情"
            visible={this.state.visible}
            onOk={this.handleOk}
            onCancel={this.handleCancel}
            footer={[
              <Button type="primary" key="back" onClick={this.handleCancel}>
                关闭
              </Button>,
            ]}
          >
            <p><span className={style.modalSpan}>订单时间：</span>{this.state.billDetailData.billdate}</p>
            <p><span className={style.modalSpan}>购买人名称：</span>{this.state.billDetailData.buyername}</p>
            <p><span className={style.modalSpan}>订单号：</span>{this.state.billDetailData.billno}</p>
            <p><span className={style.modalSpan}>商品名称：</span>{this.state.billDetailData.warename}</p>
            <p><span className={style.modalSpan}>商品图片：</span><Avatar className={style.ml5} size={60} shape="square" src={this.state.billDetailData.wareimage} /></p>
            <p><span className={style.modalSpan}>数量：</span>{this.state.billDetailData.qty}</p>
            <p><span className={style.modalSpan}>合计：</span>{this.state.billDetailData.amount}</p>
            <p><span className={style.modalSpan}>买家头像：</span><Avatar className={style.ml5} size={60} shape="square" src={this.state.billDetailData.buyeravatar} /></p>
            <p><span className={style.modalSpan}>收货地址：</span>{this.state.billDetailData.c_address}</p>
            <p><span className={style.modalSpan}>收货人：</span>{this.state.billDetailData.c_linkman}</p>
            <p><span className={style.modalSpan}>收货人手机/电话：</span>{this.state.billDetailData.c_tel}</p>
            <p><span className={style.modalSpan}>快递单号：</span>{this.state.billDetailData.express_no}</p>
            <p><span className={style.modalSpan}>快递公司：</span>{this.state.billDetailData.express_company}</p>
            <p><span className={style.modalSpan}>支付方式：</span>{this.state.billDetailData.payway==0? "在线支付":"现金支付"}</p>
            <p><span className={style.modalSpan}>付款时间：</span>{this.state.billDetailData.paydate}</p>
            <p><span className={style.modalSpan}>店铺名称：</span>{this.state.billDetailData.salername}</p>
            <p><span className={style.modalSpan}>店铺logo：</span><Avatar className={style.ml5} size={60} shape="square" src={this.state.billDetailData.salerlogo} /></p>
            <p><span className={style.modalSpan}>处理完成日期：</span>{this.state.billDetailData.enddate}</p>
            <p><span className={style.modalSpan}>订单状态：</span>{this.judgeBillState(this.state.billDetailData.billstate)}</p>
          </Modal>
        </div>
        {/* </Card> */}
      </div>
    );
  }
}

export default AllOrders;