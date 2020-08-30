import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { formatMessage, FormattedMessage } from 'umi/locale';
import { Row, Col, Card, Tooltip } from 'antd';
import { Pie, WaterWave, Gauge, TagCloud } from '@/components/Charts';
import NumberInfo from '@/components/NumberInfo';
import CountDown from '@/components/CountDown';
import ActiveChart from '@/components/ActiveChart';
import numeral from 'numeral';
import GridContent from '@/components/PageHeaderWrapper/GridContent';

import Authorized from '@/utils/Authorized';
import styles from './Monitor.less';

const { Secured } = Authorized;

const targetTime = new Date().getTime() + 3900000;

// use permission as a parameter
const havePermissionAsync = new Promise((resolve) => {
  // Call resolve on behalf of passed
  setTimeout(() => resolve(), 300);
});

@Secured(havePermissionAsync)
@connect(({ monitor, loading }) => ({
  monitor,
  loading: loading.models.monitor
}))
class Monitor extends PureComponent {
  componentDidMount() {
    const { dispatch } = this.props;
    // dispatch({
    //   type: 'monitor/fetchTags'
    // });
  }

  render() {
    const { monitor, loading } = this.props;
    // const { tags } = monitor;
    const { tags } = {"tags":[{"name":"吉安市","value":35,"type":1},{"name":"鞍山市","value":18,"type":2},{"name":"厦门市","value":1,"type":2},{"name":"海外","value":96,"type":1},{"name":"北京市","value":38,"type":2},{"name":"榆林市","value":7,"type":1},{"name":"文山壮族苗族自治州","value":62,"type":1},{"name":"台州市","value":24,"type":2},{"name":"邯郸市","value":81,"type":1},{"name":"伊犁哈萨克自治州","value":9,"type":0},{"name":"嘉兴市","value":79,"type":0},{"name":"呼和浩特市","value":85,"type":1},{"name":"上海市","value":84,"type":1},{"name":"陇南市","value":49,"type":1},{"name":"上海市","value":71,"type":0},{"name":"贵港市","value":47,"type":2},{"name":"巢湖市","value":59,"type":1},{"name":"日照市","value":31,"type":2},{"name":"六盘水市","value":37,"type":2},{"name":"山南地区","value":83,"type":2},{"name":"东莞市","value":66,"type":2},{"name":"喀什地区","value":49,"type":2},{"name":"北京市","value":39,"type":1},{"name":"重庆市","value":31,"type":0},{"name":"宝鸡市","value":26,"type":1},{"name":"佳木斯市","value":63,"type":2},{"name":"庆阳市","value":68,"type":2},{"name":"黄南藏族自治州","value":5,"type":0},{"name":"西双版纳傣族自治州","value":75,"type":0},{"name":"邵阳市","value":75,"type":1},{"name":"澎湖县","value":79,"type":0},{"name":"喀什地区","value":1,"type":1},{"name":"信阳市","value":92,"type":1},{"name":"东营市","value":30,"type":2},{"name":"辽阳市","value":22,"type":0},{"name":"石嘴山市","value":85,"type":1},{"name":"海外","value":84,"type":1},{"name":"香港岛","value":86,"type":1},{"name":"曲靖市","value":89,"type":1},{"name":"三亚市","value":28,"type":0},{"name":"固原市","value":33,"type":0},{"name":"白城市","value":68,"type":1},{"name":"鄂尔多斯市","value":16,"type":2},{"name":"南平市","value":28,"type":1},{"name":"六盘水市","value":71,"type":0},{"name":"通化市","value":55,"type":0},{"name":"白银市","value":97,"type":1},{"name":"海外","value":39,"type":1},{"name":"枣庄市","value":40,"type":1},{"name":"平凉市","value":67,"type":1},{"name":"黔南布依族苗族自治州","value":86,"type":1},{"name":"益阳市","value":73,"type":2},{"name":"上海市","value":67,"type":1},{"name":"天津市","value":43,"type":1},{"name":"阳泉市","value":63,"type":0},{"name":"山南地区","value":64,"type":2},{"name":"钦州市","value":59,"type":0},{"name":"衡水市","value":90,"type":1},{"name":"海口市","value":80,"type":0},{"name":"赤峰市","value":46,"type":1},{"name":"宜兰县","value":64,"type":0},{"name":"松原市","value":87,"type":1},{"name":"上海市","value":83,"type":1},{"name":"滨州市","value":56,"type":0},{"name":"临夏回族自治州","value":35,"type":0},{"name":"固原市","value":99,"type":0},{"name":"克拉玛依市","value":60,"type":1},{"name":"开封市","value":44,"type":1},{"name":"滨州市","value":80,"type":2},{"name":"牡丹江市","value":57,"type":0},{"name":"温州市","value":12,"type":1},{"name":"鹤岗市","value":96,"type":0},{"name":"宜宾市","value":46,"type":1},{"name":"淮安市","value":97,"type":0},{"name":"红河哈尼族彝族自治州","value":54,"type":1},{"name":"商洛市","value":93,"type":1},{"name":"张家口市","value":73,"type":2},{"name":"南京市","value":63,"type":2},{"name":"保定市","value":51,"type":2},{"name":"上海市","value":42,"type":1},{"name":"吴忠市","value":37,"type":2},{"name":"贺州市","value":63,"type":2},{"name":"亳州市","value":30,"type":0},{"name":"抚顺市","value":37,"type":1},{"name":"衡阳市","value":57,"type":2},{"name":"蚌埠市","value":4,"type":2},{"name":"天津市","value":65,"type":0},{"name":"贵港市","value":91,"type":2},{"name":"昌都地区","value":94,"type":0},{"name":"塔城地区","value":43,"type":0},{"name":"昌吉回族自治州","value":87,"type":1},{"name":"黔东南苗族侗族自治州","value":82,"type":1},{"name":"酒泉市","value":60,"type":2},{"name":"上海市","value":10,"type":1},{"name":"青岛市","value":99,"type":2},{"name":"广安市","value":87,"type":0},{"name":"长沙市","value":19,"type":1},{"name":"合肥市","value":68,"type":1},{"name":"本溪市","value":65,"type":2},{"name":"北京市","value":40,"type":2}]};


    return (
      <GridContent>
        <Row gutter={24}>
          <Col xl={18} lg={24} md={24} sm={24} xs={24} style={{ marginBottom: 24 }}>
            <Card
              title={
                <FormattedMessage
                  id="app.monitor.trading-activity"
                  defaultMessage="Real-Time Trading Activity"
                />
              }
              bordered={false}
            >
              <Row>
                <Col md={6} sm={12} xs={24}>
                  <NumberInfo
                    subTitle={
                      <FormattedMessage
                        id="app.monitor.total-transactions"
                        defaultMessage="Total transactions today"
                      />
                    }
                    suffix="元"
                    total={numeral(124543233).format('0,0')}
                  />
                </Col>
                <Col md={6} sm={12} xs={24}>
                  <NumberInfo
                    subTitle={
                      <FormattedMessage
                        id="app.monitor.sales-target"
                        defaultMessage="Sales target completion rate"
                      />
                    }
                    total="92%"
                  />
                </Col>
                <Col md={6} sm={12} xs={24}>
                  <NumberInfo
                    subTitle={
                      <FormattedMessage
                        id="app.monitor.remaining-time"
                        defaultMessage="Remaining time of activity"
                      />
                    }
                    total={<CountDown target={targetTime} />}
                  />
                </Col>
                <Col md={6} sm={12} xs={24}>
                  <NumberInfo
                    subTitle={
                      <FormattedMessage
                        id="app.monitor.total-transactions-per-second"
                        defaultMessage="Total transactions per second"
                      />
                    }
                    suffix="元"
                    total={numeral(234).format('0,0')}
                  />
                </Col>
              </Row>
              <div className={styles.mapChart}>
                <Tooltip
                  title={
                    <FormattedMessage
                      id="app.monitor.waiting-for-implementation"
                      defaultMessage="Waiting for implementation"
                    />
                  }
                >
                  <img
                    src="https://gw.alipayobjects.com/zos/rmsportal/HBWnDEUXCnGnGrRfrpKa.png"
                    alt="map"
                  />
                </Tooltip>
              </div>
            </Card>
          </Col>
          <Col xl={6} lg={24} md={24} sm={24} xs={24}>
            <Card
              title={
                <FormattedMessage
                  id="app.monitor.activity-forecast"
                  defaultMessage="Activity forecast"
                />
              }
              style={{ marginBottom: 24 }}
              bordered={false}
            >
              <ActiveChart />
            </Card>
            <Card
              title={<FormattedMessage id="app.monitor.efficiency" defaultMessage="Efficiency" />}
              style={{ marginBottom: 24 }}
              bodyStyle={{ textAlign: 'center' }}
              bordered={false}
            >
              <Gauge
                title={formatMessage({ id: 'app.monitor.ratio', defaultMessage: 'Ratio' })}
                height={180}
                percent={87}
              />
            </Card>
          </Col>
        </Row>
        <Row gutter={24}>
          <Col xl={12} lg={24} sm={24} xs={24}>
            <Card
              title={
                <FormattedMessage
                  id="app.monitor.proportion-per-category"
                  defaultMessage="Proportion Per Category"
                />
              }
              bordered={false}
              className={styles.pieCard}
            >
              <Row style={{ padding: '16px 0' }}>
                <Col span={8}>
                  <Pie
                    animate={false}
                    percent={28}
                    subTitle={
                      <FormattedMessage id="app.monitor.fast-food" defaultMessage="Fast food" />
                    }
                    total="28%"
                    height={128}
                    lineWidth={2}
                  />
                </Col>
                <Col span={8}>
                  <Pie
                    animate={false}
                    color="#5DDECF"
                    percent={22}
                    subTitle={
                      <FormattedMessage
                        id="app.monitor.western-food"
                        defaultMessage="Western food"
                      />
                    }
                    total="22%"
                    height={128}
                    lineWidth={2}
                  />
                </Col>
                <Col span={8}>
                  <Pie
                    animate={false}
                    color="#2FC25B"
                    percent={32}
                    subTitle={
                      <FormattedMessage id="app.monitor.hot-pot" defaultMessage="Hot pot" />
                    }
                    total="32%"
                    height={128}
                    lineWidth={2}
                  />
                </Col>
              </Row>
            </Card>
          </Col>
          <Col xl={6} lg={12} sm={24} xs={24}>
            <Card
              title={
                <FormattedMessage
                  id="app.monitor.popular-searches"
                  defaultMessage="Popular Searches"
                />
              }
              loading={loading}
              bordered={false}
              bodyStyle={{ overflow: 'hidden' }}
            >
              <TagCloud data={tags} height={161} />
            </Card>
          </Col>
          <Col xl={6} lg={12} sm={24} xs={24}>
            <Card
              title={
                <FormattedMessage
                  id="app.monitor.resource-surplus"
                  defaultMessage="Resource Surplus"
                />
              }
              bodyStyle={{ textAlign: 'center', fontSize: 0 }}
              bordered={false}
            >
              <WaterWave
                height={161}
                title={
                  <FormattedMessage id="app.monitor.fund-surplus" defaultMessage="Fund Surplus" />
                }
                percent={34}
              />
            </Card>
          </Col>
        </Row>
      </GridContent>
    );
  }
}

export default Monitor;
