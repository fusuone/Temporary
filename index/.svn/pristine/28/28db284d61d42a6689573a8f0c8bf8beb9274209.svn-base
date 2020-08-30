import React from 'react';
import { Divider, Calendar, Badge, Card, Col, Modal, Button} from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import moment from 'moment';
import { List } from 'echarts/lib/export';
import styles from './Record.less';


@connect(({
    user
  }) => ({
    currentUser: user.currentUser
  }))
class Record extends React.PureComponent{
  constructor(props){
    super(props);
    this.state={
        RecordData: [],
        detailModalVisible: false,
        modal_dtae: "",
        detailModalData: {},
    };
  };

  componentDidMount() {
    this.getRecordData();
  }

  // 获取考勤数据
  getRecordData = (userno) => {
    const { getLoading, reqParams } = this.state;
    if (getLoading) return;
    this.setState({ getLoading: true });
    http({
      method: 'post',
      api: 'getattenddetail',
      params: {
        userno: this.props.currentUser.userno,
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
        message.info(msg);
        this.setState({
          RecordData: [],
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
        //error没有记录  warning迟到 success正常
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
            <Badge className={styles.badge} status={item.type} text={item.content} />
          </li>
        ))}
      </ul>
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


  render(){
    const { detailModalData, modal_dtae } = this.state;
    return(
      <PageHeaderWrapper>
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
        <Modal
          title={`${modal_dtae}的考勤详情`}
          visible={this.state.detailModalVisible}
          onCancel={this.detailHandleCancel}
          //width={1000}
          maskClosable={false}
          footer={[
            <Button type="primary" key="back" onClick={this.detailHandleCancel}>关闭</Button>
          ]}
        >
          <div className={styles.detailContainer}>
            <p><span>上午签到时间：</span><span className="content">{detailModalData.startworktime}</span></p>
            <p><span>上午签到地点：</span><span className="content">{detailModalData.endworkaddress}</span></p>
            <p><span>下午签到时间：</span><span className="content">{detailModalData.endworktime}</span></p>
            <p><span>下午签到地点：</span><span className="content">{detailModalData.endworkaddress}</span></p>
          </div>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}


export default Record;