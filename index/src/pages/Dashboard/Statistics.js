import React, { Component } from 'react';
import { connect } from 'dva';
import http from '@/utils/http';
import {
  Row,
  Col,
  Icon,
  Card,
  Tabs,
  Table,
  Radio,
  DatePicker,
  Tooltip,
  Menu,
  Dropdown,
  Button 
} from 'antd';
import { TimelineChart, Bar } from '@/components/Charts';
// 引入 ECharts 主模块
import echarts from 'echarts/lib/echarts';
// 引入柱状图
import  'echarts/lib/chart/bar';
import  'echarts/lib/chart/pie';
import  'echarts/lib/chart/line';
// 引入提示框和标题组件
import 'echarts/lib/component/tooltip';
import 'echarts/lib/component/title';
import 'echarts/lib/component/legend';
import 'echarts/lib/component/toolbox';
import 'echarts/lib/component/grid';
import styles from './Statistics.less';
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))

class Statistics extends Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      reqParams: {
        admin: props.currentUser.admin,
        datapart: 'Y'
        
      // ...initialSearchParams
      },
      
      //营销状况
      xsxAxisData2: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
      cgxAxisData2: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
      //当年仓存情况数据
      data2:[
        {value:0, name:'进货'},
        {value:0, name:'出货'},
        {value:0, name:'退货'},
      ],

      //进出仓情况图表x轴数据
      xAxisData3: [],
      //进仓数据
      inData: [],
      //出仓数据
      outData: [],

      
    };
    
  }

  componentDidMount=()=> {
    this.initEcharts();
    this.getList();
  } 

  initEcharts = ()=>{
    // 销售额、采购额表
    const myChart = echarts.init(document.getElementById('main'));
    // 绘制图表
    //营销情况数据
    const optionData = {
      title : {
        text: '营销情况',
        x:'5px',
        y: '0px',
      },
      barGap: "0",
      grid: {
        top: 60,
        left: '2%',
        right: '6%',
        bottom: '5%',
        containLabel: true
      },
      tooltip : {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
          type : 'cross',        // 默认为直线，可选为：'line' | 'shadow'
          crossStyle: {
              color: 'red'
          }
        },
        // formatter: ": {c}元"
      },
      toolbox: {
        // top: '10px',
        y: '10',
        x: '75%',
        feature: {
          dataView: {show: true, readOnly: false},
          magicType: {show: true, type: ['line', 'bar']},
          restore: {show: true},
          saveAsImage: {show: true}
        }
      },
      legend: {
          x: 'center',
          y: '10px',
          data:['销售额','采购额']
      },
      xAxis: [
          {
              type: 'category',
              data: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
              axisPointer: {
                  type: 'shadow'
              }
          }
      ],
      yAxis: [
          {
              type: 'value',
              name: '千元',
              // interval: 50,
              axisLabel: {
                  formatter: '{value}'
              },
              splitLine: {
                  show: false
              },
              max: function(value) {
                  return value.max + 5;
              }
          }
      ],
      series : [
        {
          name:'销售额',
          type:'bar',
          color: '#30E37a',
          // data:[2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3],
          data: this.state.xsxAxisData2
        },
        {
          name:'采购额',
          type:'bar',
          color: '#8fd3f4',
          // data:[2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3],
          data: this.state.cgxAxisData2
        }
      ]
    };
    myChart.setOption(optionData);

   // 仓存表
   const myChart1 = echarts.init(document.getElementById('warehouse'));
    //仓存数据
   const optionData2 = {
     title : {
       text: '当年仓存情况',
       x:'15px',
       y: '20px',
     },
     tooltip: {
         trigger: 'item',
         formatter: "{a} <br/>{b}: {c} ({d}%)"
     },
     legend: {
         orient: 'vertical',
         x: '15px',
         y: '60px',
         data:['进货','出货','退货']
     },
     series: [
      {
        name:'当年仓存情况',
        type: 'pie',
        radius : '75%',
        center: ['60%', '50%'],
        color: ['#51eaea', '#c7004c', '#ff9d76'],
        selectedMode: 'single',
        data: this.state.data2,
        itemStyle: {
            emphasis: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }
     ]
    };
   // 绘制图表
   myChart1.setOption(optionData2);

   // 进出仓表
   const myChart2 = echarts.init(document.getElementById('inAndOUt'));
   // 绘制图表
   //进出仓图标数据
   const optionData3 =  {
    title : {
      text: '进出仓情况',
      x:'5px',
      y: '0px',
    },
    barGap: "0",
    grid: {
      top: 60,
      left: '2%',
      right: '6%',
      bottom: '5%',
      containLabel: true
    },
    tooltip : {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
            type : 'cross',        // 默认为直线，可选为：'line' | 'shadow'
            crossStyle: {
              color: 'red'
            }
        },
        // formatter: ": {c}元"
    },
    toolbox: {
      // top: '10px',
      y: '10',
      x: '75%',
      feature: {
        dataView: {show: true, readOnly: false},
        magicType: {show: true, type: ['line', 'bar']},
        restore: {show: true},
        saveAsImage: {show: true}
      }
    },
    legend: {
      x: 'center',
      y: '10px',
      data:['进仓','出仓']
    },
    xAxis: [
      {
        type: 'category',
        data: this.state.xAxisData3,
        axisPointer: {
            type: 'shadow'
        }
      }
    ],
    yAxis: [
      {
        type: 'value',
        name: '千元',
        // interval: 50,
        axisLabel: {
            formatter: '{value}'
        },
        splitLine: {
            show: false
        },
        max: function(value) {
          return value.max + 5;
        }
      }
    ],
    series : [
      {
          name:'进仓',
          type:'bar',
          color: '#30E332',
          data: this.state.inData
      },
      {
          name:'出仓',
          type:'bar',
          color: 'red',
          data: this.state.outData
      }
    ]
    }
   myChart2.setOption(optionData3);
  }
  selectDate = (event) =>{
    let selectValue=event.target.value
    let d = 'Y';
    switch(selectValue){
      case 'a':
        d = "Y";
        break;
      case 'b':
        d = "M";
        break;
      case 'c':
        d = "W";
        break;
      case 'd':
        d = "D";
        break;
      case 'e':
        d = "Y";
        break;
    }
    this.state.reqParams.datapart = d;
    this.getList();  
  }

  getList = () => {
    const { reqParams } = this.state;
    http({
      method: 'get',
      api: 'getstockchart',
      params: {
        ...reqParams
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        const { businesspichart, sellbuychart, workerchart } = data;
        const { skinamout, skoutamout, backamout, allamout} = businesspichart[0];
        //更新仓存情况数据
        this.setState({
          data2:[
            {value:skinamout, name:'进货'},
            {value:skoutamout, name:'出货'},
            {value:backamout, name:'退货'},
          ],
          getLoading: false
        });
        //更新营销情况数据
        for(let i=1;i<=12;i++){
          sellbuychart.map((value,key)=>{
            if(value.mon==i){
              this.state.xsxAxisData2[i-1]=value.am1;
              this.state.cgxAxisData2[i-1]=value.am2;
            }
          });
        }
        //更新进出仓情况
        workerchart.map((v,k)=>{
          this.state.xAxisData3[k]=v.username;
          this.state.inData[k]=v.insum;
          this.state.outData[k]=v.outsum;
        });
        if(workerchart.length==0){
          this.state.xAxisData3=[];
          this.state.inData=[];
          this.state.outData=[];
        }


        //从新绘制图表
       this.initEcharts();
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
  render=()=> {
    return (
      <div>
        <Card
          title={
            <div>
              <Radio.Group onChange={this.selectDate} defaultValue="a" buttonStyle="solid">
                <Radio.Button value="a">当年</Radio.Button>
                <Radio.Button value="b">当月</Radio.Button>
                <Radio.Button value="c">当周</Radio.Button>
                <Radio.Button value="d">当日</Radio.Button>
                <Radio.Button value="e">去年</Radio.Button>
              </Radio.Group>
            </div>
          }
        >
          <Row gutter={24}>
            <Col
              xs={24}
              sm={24}
              md={24}
              lg={24}
              xl={12}
              xxl={12}
            >
              <div id="inAndOUt" className={styles.inAndOut}></div>
            </Col>
            <Col
              xs={24}
              sm={24}
              md={24}
              lg={24}
              xl={12}
              xxl={12}
            >
              <div id="main" className={styles.main}></div>
            </Col>
            <Col
              xs={24}
              sm={24}
              md={24}
              lg={24}
              xl={12}
              xxl={12}
            >
              <div id="warehouse" className={styles.warehouse}></div>
            </Col>
          </Row>
        </Card>
      {/* <Button 
        type="primary"
        onClick={this.getList}
      >
        Primary</Button> */}
      </div>
    );
  }
}

export default Statistics;