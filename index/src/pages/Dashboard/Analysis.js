import React, { Component } from 'react';
import { connect } from 'dva';
import { formatMessage, FormattedMessage } from 'umi/locale';
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
import {
  ChartCard,
  MiniArea,
  MiniBar,
  MiniProgress,
  Field,
  Bar,
  Pie,
  Radar,
  TimelineChartS,
  TimelineChart
} from '@/components/Charts';
import Trend from '@/components/Trend';
import NumberInfo from '@/components/NumberInfo';
import numeral from 'numeral';
import GridContent from '@/components/PageHeaderWrapper/GridContent';
import Yuan from '@/utils/Yuan';
import { getTimeDistance } from '@/utils/utils';
import http from '@/utils/http';
import moment from 'moment';
import styles from './Analysis.less';

const { TabPane } = Tabs;
const { RangePicker } = DatePicker;

const rankingListData = [];
for (let i = 0; i < 7; i += 1) {
  rankingListData.push({
    title: `工专路 ${i} 号店`,
    total: 323234
  });
}

/////////////////////
const radarOriginData = [
  {
    name: '个人',
    ref: 10,
    koubei: 8,
    output: 4,
    contribute: 5,
    hot: 7,
  },
  {
    name: '团队',
    ref: 3,
    koubei: 9,
    output: 6,
    contribute: 3,
    hot: 1,
  },
  {
    name: '部门',
    ref: 4,
    koubei: 1,
    output: 6,
    contribute: 5,
    hot: 7,
  },
];
const radarData = [];
const radarTitleMap = {
  ref: '引用',
  koubei: '口碑',
  output: '产量',
  contribute: '贡献',
  hot: '热度',
};
radarOriginData.forEach((item) => {
  Object.keys(item).forEach((key) => {
    if (key !== 'name') {
      radarData.push({
        name: item.name,
        label: radarTitleMap[key],
        value: item[key],
      });
    }
  });
});
////////////////////

// @connect(({ chart, loading }) => ({
//   chart,
//   loading: loading.effects['chart/fetch']
// }))

// /////////////////
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))


// //////////



class Analysis extends Component {
  constructor(props) {
    super(props);
    this.rankingListData = [];
    for (let i = 0; i < 7; i += 1) {
      this.rankingListData.push({
        title: formatMessage({ id: 'app.analysis.test' }, { no: i }),
        total: 323234
      });
    }
    this.state = {
      //////////////////////////lzh
      initiaSalesData: [], //销售额初始数据
      salesData: [],
      salesDataTop: [], //销售额排行信息
      initiaVisitsData: [], //客访量初始数据
      visitsData: [],
      visitsDataTop: [], //客访量排行信息
      visitsMonth: [], //月客访量
      payBillMonth: [], //支付笔数
      salesMoneyMonth: [], //月销售额

      //////////////////////////
      salesType: 'all',
      currentTabKey: '',
      rangePickerValue: getTimeDistance('year'),
      loading: true,
      reqParams: {
        admin: props.currentUser.userno,
        datapart: 'Y'
        
      // ...initialSearchParams
      }
    };
  }
  state = {
    salesType: 'all',
    currentTabKey: '',
    rangePickerValue: getTimeDistance('year'),
    loading: true
  };









  componentDidMount() {
    this.getStockData();
    this.getVisitsData();
    // const { dispatch } = this.props;
    this.reqRef = requestAnimationFrame(() => {
    //   dispatch({
    //     type: 'chart/fetch'
    //   });
      this.timeoutId = setTimeout(() => {
        this.setState({
          loading: false
        });
      }, 600);
    });
  }

  componentWillUnmount() {
    const { dispatch } = this.props;
    // dispatch({
    //   type: 'chart/clear'
    // });
    cancelAnimationFrame(this.reqRef);
    clearTimeout(this.timeoutId);
  }

  handleChangeSalesType = (e) => {
    this.setState({
      salesType: e.target.value
    });
  };

  handleTabChange = (key) => {
    this.setState({
      currentTabKey: key
    });
  };

  handleRangePickerChange = (rangePickerValue,dateString) => {
    this.setState({
      rangePickerValue
    });
    http({
      method: 'get',
      api: 'getstockmoney',
      params: {
        admin: this.props.currentUser.admin,
        model: "1",
        startdate: dateString[0],
        enddate: dateString[1],
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          salesData: data.salesData,
          salesDataTop: data.salesDataTop
        });
      } else {
        message.warn(msg);
      }
    }).catch(() => {
    });

    http({
      method: 'get',
      api: 'getcustomervisits',
      params: {
        admin: this.props.currentUser.admin,
        model: "1",
        startdate: dateString[0],
        enddate: dateString[1],
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          visitsData: data.visitsData,
          visitsDataTop: data.visitsDataTop
        });
      } else {
        message.warn(msg);
      }
    }).catch(() => {
    });
  };


  //选择图表的显示信息
  selectDate = (type) => {
    const { initiaSalesData, initiaVisitsData} = this.state;
    switch(type){
      case 'year': 
        this.setState({
          salesData: initiaSalesData.salesData.year,
          salesDataTop: initiaSalesData.salesDataTop.year,
          visitsData: initiaVisitsData.visitsData.year,
          visitsDataTop: initiaVisitsData.visitsDataTop.year
        });
        break;
      case 'month': 
        this.setState({
          salesData: initiaSalesData.salesData.month,
          salesDataTop: initiaSalesData.salesDataTop.month,
          visitsData: initiaVisitsData.visitsData.month,
          visitsDataTop: initiaVisitsData.visitsDataTop.month
        });
        break;
      case 'week': 
        this.setState({
          salesData: initiaSalesData.salesData.week,
          salesDataTop: initiaSalesData.salesDataTop.week,
          visitsData: initiaVisitsData.visitsData.week,
          visitsDataTop: initiaVisitsData.visitsDataTop.week
        });
        break;
      case 'today': 
        this.setState({
          salesData: initiaSalesData.salesData.today,
          salesDataTop: initiaSalesData.salesDataTop.today,
          visitsData: initiaVisitsData.visitsData.today,
          visitsDataTop: initiaVisitsData.visitsDataTop.today
        });
        break;
    }
    const { dispatch } = this.props;
    this.setState({
      rangePickerValue: getTimeDistance(type)
    });

  };


  isActive(type) {
    const { rangePickerValue } = this.state;
    const value = getTimeDistance(type);
    if (!rangePickerValue[0] || !rangePickerValue[1]) {
      return '';
    }
    if (
      rangePickerValue[0].isSame(value[0], 'day') &&
      rangePickerValue[1].isSame(value[1], 'day')
    ) {
      return styles.currentDate;
    }
    return '';
  }



  //////////////////////////////////////////////////////////
  //获取销售金额
  getStockData = () => {
    http({
      method: 'get',
      api: 'getstockmoney',
      params: {
        admin: this.props.currentUser.admin
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          salesData: data.salesData.year,
          initiaSalesData: data,
          salesDataTop: data.salesDataTop.year,
          payBillMonth: data.payBillMonth,
          salesMoneyMonth: data.salesMoneyMonth,
        });
      } else {
        message.warn(msg);
      }
    }).catch(() => {
    });
  }


   //获取客户访问量
   getVisitsData = () => {
    http({
      method: 'get',
      api: 'getcustomervisits',
      params: {
        admin: this.props.currentUser.admin,
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          visitsData: data.visitsData.year,
          initiaVisitsData: data,
          visitsDataTop: data.visitsDataTop.year,
          visitsMonth: data.visitsMonth
        });
      } else {
        message.warn(msg);
      }
    }).catch(() => {
    });
  }









  ///////////////////////////////////////////////////////////////////////

  render() {
    const { rangePickerValue, salesType, loading: propsLoding, currentTabKey,   salesData, salesDataTop, visitsData, visitsDataTop, visitsMonth, payBillMonth, salesMoneyMonth } = this.state;
    const { chart, loading: stateLoading } = this.props;

    const {
      visitData,
      visitData2,
      searchData,
      offlineData,
      offlineChartData,
      salesTypeData,
      salesTypeDataOnline,
      salesTypeDataOffline
    } =  {"visitData":[{"x":"2019-05-31","y":7},{"x":"2019-06-01","y":5},{"x":"2019-06-02","y":4},{"x":"2019-06-03","y":2},{"x":"2019-06-04","y":4},{"x":"2019-06-05","y":7},{"x":"2019-06-06","y":5},{"x":"2019-06-07","y":6},{"x":"2019-06-08","y":5},{"x":"2019-06-09","y":9},{"x":"2019-06-10","y":6},{"x":"2019-06-11","y":3},{"x":"2019-06-12","y":1},{"x":"2019-06-13","y":5},{"x":"2019-06-14","y":3},{"x":"2019-06-15","y":6},{"x":"2019-06-16","y":5}],"visitData2":[{"x":"2019-05-31","y":1},{"x":"2019-06-01","y":6},{"x":"2019-06-02","y":4},{"x":"2019-06-03","y":8},{"x":"2019-06-04","y":3},{"x":"2019-06-05","y":7},{"x":"2019-06-06","y":2}],"salesData":[{"x":"1月","y":522},{"x":"2月","y":229},{"x":"3月","y":202},{"x":"4月","y":1141},{"x":"5月","y":908},{"x":"6月","y":1082},{"x":"7月","y":343},{"x":"8月","y":588},{"x":"9月","y":766},{"x":"10月","y":511},{"x":"11月","y":426},{"x":"12月","y":837}],"searchData":[{"index":1,"keyword":"搜索关键词-0","count":297,"range":9,"status":1},{"index":2,"keyword":"搜索关键词-1","count":681,"range":6,"status":0},{"index":3,"keyword":"搜索关键词-2","count":37,"range":92,"status":1},{"index":4,"keyword":"搜索关键词-3","count":983,"range":43,"status":0},{"index":5,"keyword":"搜索关键词-4","count":987,"range":41,"status":0},{"index":6,"keyword":"搜索关键词-5","count":41,"range":87,"status":0},{"index":7,"keyword":"搜索关键词-6","count":135,"range":86,"status":1},{"index":8,"keyword":"搜索关键词-7","count":262,"range":34,"status":1},{"index":9,"keyword":"搜索关键词-8","count":310,"range":15,"status":0},{"index":10,"keyword":"搜索关键词-9","count":134,"range":26,"status":1},{"index":11,"keyword":"搜索关键词-10","count":417,"range":47,"status":1},{"index":12,"keyword":"搜索关键词-11","count":139,"range":43,"status":1},{"index":13,"keyword":"搜索关键词-12","count":371,"range":10,"status":1},{"index":14,"keyword":"搜索关键词-13","count":677,"range":22,"status":1},{"index":15,"keyword":"搜索关键词-14","count":303,"range":51,"status":0},{"index":16,"keyword":"搜索关键词-15","count":0,"range":3,"status":0},{"index":17,"keyword":"搜索关键词-16","count":788,"range":93,"status":1},{"index":18,"keyword":"搜索关键词-17","count":267,"range":79,"status":1},{"index":19,"keyword":"搜索关键词-18","count":515,"range":38,"status":0},{"index":20,"keyword":"搜索关键词-19","count":994,"range":62,"status":1},{"index":21,"keyword":"搜索关键词-20","count":348,"range":67,"status":1},{"index":22,"keyword":"搜索关键词-21","count":254,"range":45,"status":0},{"index":23,"keyword":"搜索关键词-22","count":812,"range":15,"status":1},{"index":24,"keyword":"搜索关键词-23","count":457,"range":45,"status":0},{"index":25,"keyword":"搜索关键词-24","count":727,"range":89,"status":1},{"index":26,"keyword":"搜索关键词-25","count":282,"range":56,"status":1},{"index":27,"keyword":"搜索关键词-26","count":73,"range":8,"status":0},{"index":28,"keyword":"搜索关键词-27","count":26,"range":63,"status":1},{"index":29,"keyword":"搜索关键词-28","count":676,"range":28,"status":0},{"index":30,"keyword":"搜索关键词-29","count":384,"range":92,"status":1},{"index":31,"keyword":"搜索关键词-30","count":905,"range":60,"status":1},{"index":32,"keyword":"搜索关键词-31","count":651,"range":66,"status":1},{"index":33,"keyword":"搜索关键词-32","count":697,"range":10,"status":1},{"index":34,"keyword":"搜索关键词-33","count":178,"range":37,"status":1},{"index":35,"keyword":"搜索关键词-34","count":37,"range":12,"status":1},{"index":36,"keyword":"搜索关键词-35","count":816,"range":92,"status":1},{"index":37,"keyword":"搜索关键词-36","count":165,"range":96,"status":1},{"index":38,"keyword":"搜索关键词-37","count":461,"range":45,"status":1},{"index":39,"keyword":"搜索关键词-38","count":729,"range":28,"status":1},{"index":40,"keyword":"搜索关键词-39","count":544,"range":71,"status":0},{"index":41,"keyword":"搜索关键词-40","count":289,"range":11,"status":1},{"index":42,"keyword":"搜索关键词-41","count":790,"range":27,"status":0},{"index":43,"keyword":"搜索关键词-42","count":582,"range":23,"status":0},{"index":44,"keyword":"搜索关键词-43","count":627,"range":14,"status":0},{"index":45,"keyword":"搜索关键词-44","count":724,"range":21,"status":1},{"index":46,"keyword":"搜索关键词-45","count":872,"range":56,"status":0},{"index":47,"keyword":"搜索关键词-46","count":69,"range":98,"status":0},{"index":48,"keyword":"搜索关键词-47","count":489,"range":33,"status":0},{"index":49,"keyword":"搜索关键词-48","count":585,"range":63,"status":1},{"index":50,"keyword":"搜索关键词-49","count":349,"range":41,"status":1}],"offlineData":[{"name":"Stores 0","cvr":0.6},{"name":"Stores 1","cvr":0.1},{"name":"Stores 2","cvr":0.4},{"name":"Stores 3","cvr":0.1},{"name":"Stores 4","cvr":0.7},{"name":"Stores 5","cvr":0.3},{"name":"Stores 6","cvr":0.1},{"name":"Stores 7","cvr":0.1},{"name":"Stores 8","cvr":0.8},{"name":"Stores 9","cvr":0.1}],"offlineChartData":[{"x":1559265582718,"y1":11,"y2":68},{"x":1559267382718,"y1":73,"y2":21},{"x":1559269182718,"y1":95,"y2":81},{"x":1559270982718,"y1":71,"y2":16},{"x":1559272782718,"y1":23,"y2":10},{"x":1559274582718,"y1":37,"y2":61},{"x":1559276382718,"y1":96,"y2":57},{"x":1559278182718,"y1":14,"y2":82},{"x":1559279982718,"y1":18,"y2":54},{"x":1559281782718,"y1":51,"y2":98},{"x":1559283582718,"y1":64,"y2":93},{"x":1559285382718,"y1":105,"y2":87},{"x":1559287182718,"y1":30,"y2":57},{"x":1559288982718,"y1":74,"y2":40},{"x":1559290782718,"y1":25,"y2":81},{"x":1559292582718,"y1":21,"y2":89},{"x":1559294382718,"y1":56,"y2":19},{"x":1559296182718,"y1":92,"y2":75},{"x":1559297982718,"y1":109,"y2":85},{"x":1559299782718,"y1":104,"y2":21}],"salesTypeData":[{"x":"家用电器","y":4544},{"x":"食用酒水","y":3321},{"x":"个护健康","y":3113},{"x":"服饰箱包","y":2341},{"x":"母婴产品","y":1231},{"x":"其他","y":1231}],"salesTypeDataOnline":[{"x":"家用电器","y":244},{"x":"食用酒水","y":321},{"x":"个护健康","y":311},{"x":"服饰箱包","y":41},{"x":"母婴产品","y":121},{"x":"其他","y":111}],"salesTypeDataOffline":[{"x":"家用电器","y":99},{"x":"食用酒水","y":188},{"x":"个护健康","y":344},{"x":"服饰箱包","y":255},{"x":"其他","y":65}],"radarData":[{"name":"个人","label":"引用","value":10},{"name":"个人","label":"口碑","value":8},{"name":"个人","label":"产量","value":4},{"name":"个人","label":"贡献","value":5},{"name":"个人","label":"热度","value":7},{"name":"团队","label":"引用","value":3},{"name":"团队","label":"口碑","value":9},{"name":"团队","label":"产量","value":6},{"name":"团队","label":"贡献","value":3},{"name":"团队","label":"热度","value":1},{"name":"部门","label":"引用","value":4},{"name":"部门","label":"口碑","value":1},{"name":"部门","label":"产量","value":6},{"name":"部门","label":"贡献","value":5},{"name":"部门","label":"热度","value":7}]};
   
   
    const loading = propsLoding || stateLoading;
    let salesPieData;
    if (salesType === 'all') {
      salesPieData = salesTypeData;
    } else {
      salesPieData = salesType === 'online' ? salesTypeDataOnline : salesTypeDataOffline;
    }
    const menu = (
      <Menu>
        <Menu.Item>操作一</Menu.Item>
        <Menu.Item>操作二</Menu.Item>
      </Menu>
    );

    const iconGroup = (
      <span className={styles.iconGroup}>
        <Dropdown overlay={menu} placement="bottomRight">
          <Icon type="ellipsis" />
        </Dropdown>
      </span>
    );

    const salesExtra = (
      <div className={styles.salesExtraWrap}>
        <div className={styles.salesExtra}>
          <a className={this.isActive('today')} onClick={() => this.selectDate('today')}>
            <FormattedMessage id="app.analysis.all-day" defaultMessage="All Day" />
          </a>
          <a className={this.isActive('week')} onClick={() => this.selectDate('week')}>
            <FormattedMessage id="app.analysis.all-week" defaultMessage="All Week" />
          </a>
          <a className={this.isActive('month')} onClick={() => this.selectDate('month')}>
            <FormattedMessage id="app.analysis.all-month" defaultMessage="All Month" />
          </a>
          <a className={this.isActive('year')} onClick={() => this.selectDate('year')}>
            <FormattedMessage id="app.analysis.all-year" defaultMessage="All Year" />
          </a>
        </div>
        <RangePicker
          value={rangePickerValue}
          onChange={this.handleRangePickerChange}
          style={{ width: 256 }}
        />
      </div>
    );

    const columns = [
      {
        title: <FormattedMessage id="app.analysis.table.rank" defaultMessage="Rank" />,
        dataIndex: 'index',
        key: 'index'
      },
      {
        title: (
          <FormattedMessage
            id="app.analysis.table.search-keyword"
            defaultMessage="Search keyword"
          />
        ),
        dataIndex: 'keyword',
        key: 'keyword',
        render: text => <a href="/">{text}</a>
      },
      {
        title: <FormattedMessage id="app.analysis.table.users" defaultMessage="Users" />,
        dataIndex: 'count',
        key: 'count',
        sorter: (a, b) => a.count - b.count,
        className: styles.alignRight
      },
      {
        title: (
          <FormattedMessage id="app.analysis.table.weekly-range" defaultMessage="Weekly Range" />
        ),
        dataIndex: 'range',
        key: 'range',
        sorter: (a, b) => a.range - b.range,
        render: (text, record) => (
          <Trend flag={record.status === 1 ? 'down' : 'up'}>
            <span style={{ marginRight: 4 }}>{text}%</span>
          </Trend>
        ),
        align: 'right'
      }
    ];

    const activeKey = currentTabKey || (offlineData[0] && offlineData[0].name);

    const CustomTab = ({ data, currentTabKey: currentKey }) => (
      <Row gutter={8} style={{ width: 138, margin: '8px 0' }}>
        <Col span={12}>
          <NumberInfo
            title={data.name}
            subTitle={
              <FormattedMessage
                id="app.analysis.conversion-rate"
                defaultMessage="Conversion Rate"
              />
            }
            gap={2}
            total={`${data.cvr * 100}%`}
            theme={currentKey !== data.name && 'light'}
          />
        </Col>
        <Col span={12} style={{ paddingTop: 36 }}>
          <Pie
            animate={false}
            color={currentKey !== data.name && '#BDE4FF'}
            inner={0.55}
            tooltip={false}
            margin={[0, 0, 0, 0]}
            percent={data.cvr * 100}
            height={64}
          />
        </Col>
      </Row>
    );

    const topColResponsiveProps = {
      xs: 24,
      sm: 12,
      md: 12,
      lg: 12,
      xl: 6,
      style: { marginBottom: 24 }
    };

    return (
      <GridContent>
        <Row gutter={24}>
          <Col {...topColResponsiveProps}>
            <ChartCard
              bordered={false}
              title={
                <FormattedMessage id="app.analysis.total-sales" defaultMessage="Total Sales" />
              }
              action={
                <Tooltip
                  title={
                    <FormattedMessage id="app.analysis.introduce" defaultMessage="introduce" />
                  }
                >
                  <Icon type="info-circle-o" />
                </Tooltip>
              }
              loading={loading}
              total={() => <Yuan>{salesMoneyMonth.all}</Yuan>}
              footer={
                <Field
                  label={
                    <FormattedMessage id="app.analysis.day-sales" defaultMessage="Day Sales" />
                  }
                  value={`￥${numeral(salesMoneyMonth.today).format('0,0')}`}
                />
              }
              contentHeight={46}
            >
              <MiniArea color="#13C2C2" data={salesMoneyMonth.salesMoneyData} />
              {/* <Trend flag="up" style={{ marginRight: 16 }}>
                <FormattedMessage id="app.analysis.week" defaultMessage="Weekly Changes" />
                <span className={styles.trendText}>12%</span>
              </Trend>
              <Trend flag="down">
                <FormattedMessage id="app.analysis.day" defaultMessage="Daily Changes" />
                <span className={styles.trendText}>11%</span>
              </Trend> */}
              



            </ChartCard>
          </Col>
          <Col {...topColResponsiveProps}>
            <ChartCard
              bordered={false}
              loading={loading}
              title={<FormattedMessage id="app.analysis.month-visits" defaultMessage="visits" />}
              action={
                <Tooltip
                  title={
                    <FormattedMessage id="app.analysis.introduce" defaultMessage="introduce" />
                  }
                >
                  <Icon type="info-circle-o" />
                </Tooltip>
              }
              total={numeral(visitsMonth.all).format('0,0')}
              footer={
                <Field
                  label={
                    <FormattedMessage id="app.analysis.day-visits" defaultMessage="Day Visits" />
                  }
                  value={numeral(visitsMonth.today).format('0,0')}
                />
              }
              contentHeight={46}
            >
              <MiniArea color="#975FE4" data={visitsMonth.visitsData} />
            </ChartCard>
          </Col>
          <Col {...topColResponsiveProps}>
            <ChartCard
              bordered={false}
              loading={loading}
              title={<FormattedMessage id="app.analysis.payments" defaultMessage="Payments" />}
              action={
                <Tooltip
                  title={
                    <FormattedMessage id="app.analysis.introduce" defaultMessage="Introduce" />
                  }
                >
                  <Icon type="info-circle-o" />
                </Tooltip>
              }
              total={numeral(payBillMonth.all).format('0,0')}
              footer={
                <Field
                  label={
                    <FormattedMessage
                      id="app.analysis.conversion-rate"
                      defaultMessage="Conversion Rate"
                    />
                  }
                  value={payBillMonth.today}
                />
              }
              contentHeight={46}
            >
              <MiniBar data={payBillMonth.payBillData} />
            </ChartCard>
          </Col>
          <Col {...topColResponsiveProps}>
            <ChartCard
              loading={loading}
              bordered={false}
              title={
                <FormattedMessage
                  id="app.analysis.operational-effect"
                  defaultMessage="Operational Effect"
                />
              }
              action={
                <Tooltip
                  title={
                    <FormattedMessage id="app.analysis.introduce" defaultMessage="introduce" />
                  }
                >
                  <Icon type="info-circle-o" />
                </Tooltip>
              }
              total="0%"
              footer={
                <div style={{ whiteSpace: 'nowrap', overflow: 'hidden' }}>
                  <Trend flag="up" style={{ marginRight: 16 }}>
                    <FormattedMessage id="app.analysis.week" defaultMessage="Weekly changes" />
                    <span className={styles.trendText}>0%</span>
                  </Trend>
                  <Trend flag="down">
                    <FormattedMessage id="app.analysis.day" defaultMessage="Weekly changes" />
                    <span className={styles.trendText}>0%</span>
                  </Trend>
                </div>
              }
              contentHeight={46}
            >
              <MiniProgress percent={0} strokeWidth={8} target={0} color="#13C2C2" />
            </ChartCard>
          </Col>
        </Row>

        <Card loading={loading} bordered={false} bodyStyle={{ padding: 0 }}>
          <div className={styles.salesCard}>
            <Tabs tabBarExtraContent={salesExtra} size="large" tabBarStyle={{ marginBottom: 24 }}>
              <TabPane
                tab={<FormattedMessage id="app.analysis.sales" defaultMessage="Sales" />}
                key="sales"
              >
                <Row>
                  <Col xl={16} lg={12} md={12} sm={24} xs={24}>
                    <div className={styles.salesBar}>
                      <Bar
                        height={400}
                        title={
                          <FormattedMessage
                            id="app.analysis.sales-trend"
                            defaultMessage="Sales Trend"
                          />
                        }
                        data={salesData}
                      />
                    </div>
                  </Col>
                  <Col xl={8} lg={12} md={12} sm={24} xs={24}>
                    <div className={styles.salesRank}>
                      <h4 className={styles.rankingTitle}>
                        <FormattedMessage
                          id="app.analysis.sales-ranking"
                          defaultMessage="Sales Ranking"
                        />
                      </h4>
                      <ul className={styles.rankingList}>
                        {salesDataTop.map((item, i) => (
                          <li key={item.title}>
                            <span
                              className={`${styles.rankingItemNumber} ${
                                i < 3 ? styles.active : ''
                              }`}
                            >
                              {i + 1}
                            </span>
                            <span className={styles.rankingItemTitle} title={item.title}>
                              {item.title}
                            </span>
                            <span className={styles.rankingItemValue}>
                              {numeral(item.total).format('0,0')}
                            </span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  </Col>
                </Row>
              </TabPane>
              <TabPane
                tab={<FormattedMessage id="app.analysis.visits" defaultMessage="Visits" />}
                key="views"
              >
                <Row>
                  <Col xl={16} lg={12} md={12} sm={24} xs={24}>
                    <div className={styles.salesBar}>
                      <Bar
                        height={292}
                        title={
                          <FormattedMessage
                            id="app.analysis.visits-trend"
                            defaultMessage="Visits Trend"
                          />
                        }
                        data={visitsData}
                      />
                    </div>
                  </Col>
                  <Col xl={8} lg={12} md={12} sm={24} xs={24}>
                    <div className={styles.salesRank}>
                      <h4 className={styles.rankingTitle}>
                        <FormattedMessage
                          id="app.analysis.visits-ranking"
                          defaultMessage="Visits Ranking"
                        />
                      </h4>
                      <ul className={styles.rankingList}>
                        {visitsDataTop.map((item, i) => (
                          <li key={item.title}>
                            <span
                              className={`${styles.rankingItemNumber} ${
                                i < 3 ? styles.active : ''
                              }`}
                            >
                              {i + 1}
                            </span>
                            <span className={styles.rankingItemTitle} title={item.title}>
                              {item.title}
                            </span>
                            <span>{numeral(item.total).format('0,0')}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  </Col>
                </Row>
              </TabPane>
            </Tabs>
          </div>
        </Card>

        <Row gutter={24}>
          <Col xl={12} lg={24} md={24} sm={24} xs={24}>
            <Card
              loading={loading}
              bordered={false}
              title={
                <FormattedMessage
                  id="app.analysis.online-top-search"
                  defaultMessage="Online Top Search"
                />
              }
              extra={iconGroup}
              style={{ marginTop: 24 }}
            >
              <Row gutter={68}>
                <Col sm={12} xs={24} style={{ marginBottom: 24 }}>
                  <NumberInfo
                    subTitle={
                      <span>
                        <FormattedMessage
                          id="app.analysis.search-users"
                          defaultMessage="search users"
                        />
                        <Tooltip
                          title={
                            <FormattedMessage
                              id="app.analysis.introduce"
                              defaultMessage="introduce"
                            />
                          }
                        >
                          <Icon style={{ marginLeft: 8 }} type="info-circle-o" />
                        </Tooltip>
                      </span>
                    }
                    gap={8}
                    total={numeral(12321).format('0,0')}
                    status="up"
                    subTotal={17.1}
                  />
                  <MiniArea line height={45} data={visitData2} />
                </Col>
                <Col sm={12} xs={24} style={{ marginBottom: 24 }}>
                  <NumberInfo
                    subTitle={
                      <span>
                        <FormattedMessage
                          id="app.analysis.per-capita-search"
                          defaultMessage="Per Capita Search"
                        />
                        <Tooltip
                          title={
                            <FormattedMessage
                              id="app.analysis.introduce"
                              defaultMessage="introduce"
                            />
                          }
                        >
                          <Icon style={{ marginLeft: 8 }} type="info-circle-o" />
                        </Tooltip>
                      </span>
                    }
                    total={2.7}
                    status="down"
                    subTotal={26.2}
                    gap={8}
                  />
                  <MiniArea line height={45} data={visitData2} />
                </Col>
              </Row>
              <Table
                rowKey={record => record.index}
                size="small"
                columns={columns}
                dataSource={searchData}
                pagination={{
                  style: { marginBottom: 0 },
                  pageSize: 5
                }}
              />
            </Card>
          </Col>
          <Col xl={12} lg={24} md={24} sm={24} xs={24}>
            <Card
              loading={loading}
              className={styles.salesCard}
              bordered={false}
              title={
                <FormattedMessage
                  id="app.analysis.the-proportion-of-sales"
                  defaultMessage="The Proportion of Sales"
                />
              }
              bodyStyle={{ padding: 24 }}
              extra={
                <div className={styles.salesCardExtra}>
                  {/* {iconGroup} */}
                  <div className={styles.salesTypeRadio}>
                    <Radio.Group value={salesType} onChange={this.handleChangeSalesType}>
                      <Radio.Button value="all">
                        <FormattedMessage id="app.analysis.channel.all" defaultMessage="ALL" />
                      </Radio.Button>
                      <Radio.Button value="online">
                        <FormattedMessage
                          id="app.analysis.channel.online"
                          defaultMessage="Online"
                        />
                      </Radio.Button>
                      <Radio.Button value="stores">
                        <FormattedMessage
                          id="app.analysis.channel.stores"
                          defaultMessage="Stores"
                        />
                      </Radio.Button>
                    </Radio.Group>
                  </div>
                </div>
              }
              style={{ marginTop: 24, minHeight: 509 }}
            >
              <h4 style={{ marginTop: 8, marginBottom: 32 }}>
                <FormattedMessage id="app.analysis.sales" defaultMessage="Sales" />
              </h4>
              <Pie
                hasLegend
                subTitle={<FormattedMessage id="app.analysis.sales" defaultMessage="Sales" />}
                total={() => <Yuan>{salesPieData.reduce((pre, now) => now.y + pre, 0)}</Yuan>}
                data={salesPieData}
                valueFormat={value => <Yuan>{value}</Yuan>}
                height={248}
                lineWidth={4}
              />
            </Card>
          </Col>
        </Row>

        <Card
          loading={loading}
          className={styles.offlineCard}
          bordered={false}
          bodyStyle={{ padding: '0 0 32px 0' }}
          style={{ marginTop: 32 }}
        >
          <Tabs activeKey={activeKey} onChange={this.handleTabChange}>
            {offlineData.map(shop => (
              <TabPane tab={<CustomTab data={shop} currentTabKey={activeKey} />} key={shop.name}>
                <div style={{ padding: '0 24px' }}>
                  <TimelineChart
                    height={400}
                    data={offlineChartData}
                    titleMap={{
                      y1: formatMessage({ id: 'app.analysis.traffic' }),
                      y2: formatMessage({ id: 'app.analysis.payments' })
                    }}
                  />
                </div>
              </TabPane>
            ))}
          </Tabs>
        </Card>
      </GridContent>
    );
  }
}

export default Analysis;
