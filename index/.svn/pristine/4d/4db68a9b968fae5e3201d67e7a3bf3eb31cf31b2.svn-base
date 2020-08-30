import React, { PureComponent } from 'react';
import moment from 'moment';
import { connect } from 'dva';
import Link from 'umi/link';
import { Row, Col, Card, List, Avatar } from 'antd';

import { Radar } from '@/components/Charts';
import EditableLinkGroup from '@/components/EditableLinkGroup';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

import styles from './Workplace.less';

const links = [
  {
    title: '操作一',
    href: ''
  },
  {
    title: '操作二',
    href: ''
  },
  {
    title: '操作三',
    href: ''
  },
  {
    title: '操作四',
    href: ''
  },
  {
    title: '操作五',
    href: ''
  },
  {
    title: '操作六',
    href: ''
  }
];

@connect(({ user, project, activities, chart, loading }) => ({
  currentUser: user.currentUser,
  project,
  activities,
  chart,
  currentUserLoading: loading.effects['user/fetchCurrent'],
  projectLoading: loading.effects['project/fetchNotice'],
  activitiesLoading: loading.effects['activities/fetchList']
}))
class Workplace extends PureComponent {
  componentDidMount() {
    const { dispatch } = this.props;
    // dispatch({
    //   type: 'user/fetchCurrent',
    // });
    // dispatch({
    //   type: 'project/fetchNotice'
    // });
    // dispatch({
    //   type: 'activities/fetchList'
    // });
    // dispatch({
    //   type: 'chart/fetch'
    // });
  }

  componentWillUnmount() {
    const { dispatch } = this.props;
    // dispatch({
    //   type: 'chart/clear'
    // });
  }

  renderActivities() {
   const list = [{"id":"trend-1","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"曲丽丽","avatar":"https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png"},"group":{"name":"高逼格设计天团","link":"http://github.com/"},"project":{"name":"六月迭代","link":"http://github.com/"},"template":"在 @{group} 新建项目 @{project}"},{"id":"trend-2","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"付小小","avatar":"https://gw.alipayobjects.com/zos/rmsportal/cnrhVkzwxjPwAaCfPbdc.png"},"group":{"name":"高逼格设计天团","link":"http://github.com/"},"project":{"name":"六月迭代","link":"http://github.com/"},"template":"在 @{group} 新建项目 @{project}"},{"id":"trend-3","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"林东东","avatar":"https://gw.alipayobjects.com/zos/rmsportal/gaOngJwsRYRaVAuXXcmB.png"},"group":{"name":"中二少女团","link":"http://github.com/"},"project":{"name":"六月迭代","link":"http://github.com/"},"template":"在 @{group} 新建项目 @{project}"},{"id":"trend-4","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"周星星","avatar":"https://gw.alipayobjects.com/zos/rmsportal/WhxKECPNujWoWEFNdnJE.png"},"project":{"name":"5 月日常迭代","link":"http://github.com/"},"template":"将 @{project} 更新至已发布状态"},{"id":"trend-5","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"朱偏右","avatar":"https://gw.alipayobjects.com/zos/rmsportal/ubnKSIfAJTxIgXOKlciN.png"},"project":{"name":"工程效能","link":"http://github.com/"},"comment":{"name":"留言","link":"http://github.com/"},"template":"在 @{project} 发布了 @{comment}"},{"id":"trend-6","updatedAt":"2019-05-31T02:48:00.289Z","user":{"name":"乐哥","avatar":"https://gw.alipayobjects.com/zos/rmsportal/jZUIxmJycoymBprLOUbT.png"},"group":{"name":"程序员日常","link":"http://github.com/"},"project":{"name":"品牌迭代","link":"http://github.com/"},"template":"在 @{group} 新建项目 @{project}"}];
    // const {
    //   activities: { list }
    // } = this.props;
    return list.map((item) => {
      const events = item.template.split(/@\{([^{}]*)\}/gi).map((key) => {
        if (item[key]) {
          return (
            <a href={item[key].link} key={item[key].name}>
              {item[key].name}
            </a>
          );
        }
        return key;
      });
      return (
        <List.Item key={item.id}>
          <List.Item.Meta
            avatar={<Avatar src={item.user.avatar} />}
            title={
              <span>
                <a className={styles.username}>{item.user.name}</a>
                &nbsp;
                <span className={styles.event}>{events}</span>
              </span>
            }
            description={
              <span className={styles.datetime} title={item.updatedAt}>
                {moment(item.updatedAt).fromNow()}
              </span>
            }
          />
        </List.Item>
      );
    });
  }

  render() {
    const {
      currentUser,
      currentUserLoading,
      projectLoading,
      activitiesLoading,
    } = this.props;
    const notice = [{"id":"xxx1","title":"Alipay","logo":"https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png","description":"那是一种内在的东西，他们到达不了，也无法触及的","updatedAt":"2019-05-31T02:48:00.282Z","member":"科学搬砖组","href":"","memberLink":""},{"id":"xxx2","title":"Angular","logo":"https://gw.alipayobjects.com/zos/rmsportal/zOsKZmFRdUtvpqCImOVY.png","description":"希望是一个好东西，也许是最好的，好东西是不会消亡的","updatedAt":"2017-07-24T00:00:00.000Z","member":"全组都是吴彦祖","href":"","memberLink":""},{"id":"xxx3","title":"Ant Design","logo":"https://gw.alipayobjects.com/zos/rmsportal/dURIMkkrRFpPgTuzkwnB.png","description":"城镇中有那么多的酒馆，她却偏偏走进了我的酒馆","updatedAt":"2019-05-31T02:48:00.289Z","member":"中二少女团","href":"","memberLink":""},{"id":"xxx4","title":"Ant Design Pro","logo":"https://gw.alipayobjects.com/zos/rmsportal/sfjbOqnsXXJgNCjCzDBL.png","description":"那时候我只会想自己想要什么，从不想自己拥有什么","updatedAt":"2017-07-23T00:00:00.000Z","member":"程序员日常","href":"","memberLink":""},{"id":"xxx5","title":"Bootstrap","logo":"https://gw.alipayobjects.com/zos/rmsportal/siCrBXXhmvTQGWPNLBow.png","description":"凛冬将至","updatedAt":"2017-07-23T00:00:00.000Z","member":"高逼格设计天团","href":"","memberLink":""},{"id":"xxx6","title":"React","logo":"https://gw.alipayobjects.com/zos/rmsportal/kZzEzemZyKLKFsojXItE.png","description":"生命就像一盒巧克力，结果往往出人意料","updatedAt":"2017-07-23T00:00:00.000Z","member":"骗你来学计算机","href":"","memberLink":""}];
    const {radarData}  = {"visitData":[{"x":"2019-05-31","y":7},{"x":"2019-06-01","y":5},{"x":"2019-06-02","y":4},{"x":"2019-06-03","y":2},{"x":"2019-06-04","y":4},{"x":"2019-06-05","y":7},{"x":"2019-06-06","y":5},{"x":"2019-06-07","y":6},{"x":"2019-06-08","y":5},{"x":"2019-06-09","y":9},{"x":"2019-06-10","y":6},{"x":"2019-06-11","y":3},{"x":"2019-06-12","y":1},{"x":"2019-06-13","y":5},{"x":"2019-06-14","y":3},{"x":"2019-06-15","y":6},{"x":"2019-06-16","y":5}],"visitData2":[{"x":"2019-05-31","y":1},{"x":"2019-06-01","y":6},{"x":"2019-06-02","y":4},{"x":"2019-06-03","y":8},{"x":"2019-06-04","y":3},{"x":"2019-06-05","y":7},{"x":"2019-06-06","y":2}],"salesData":[{"x":"1月","y":975},{"x":"2月","y":511},{"x":"3月","y":959},{"x":"4月","y":883},{"x":"5月","y":295},{"x":"6月","y":594},{"x":"7月","y":1198},{"x":"8月","y":254},{"x":"9月","y":265},{"x":"10月","y":652},{"x":"11月","y":414},{"x":"12月","y":1024}],"searchData":[{"index":1,"keyword":"搜索关键词-0","count":89,"range":81,"status":1},{"index":2,"keyword":"搜索关键词-1","count":48,"range":79,"status":0},{"index":3,"keyword":"搜索关键词-2","count":211,"range":52,"status":0},{"index":4,"keyword":"搜索关键词-3","count":74,"range":19,"status":1},{"index":5,"keyword":"搜索关键词-4","count":49,"range":23,"status":0},{"index":6,"keyword":"搜索关键词-5","count":4,"range":52,"status":1},{"index":7,"keyword":"搜索关键词-6","count":466,"range":2,"status":0},{"index":8,"keyword":"搜索关键词-7","count":10,"range":4,"status":1},{"index":9,"keyword":"搜索关键词-8","count":275,"range":15,"status":0},{"index":10,"keyword":"搜索关键词-9","count":314,"range":99,"status":0},{"index":11,"keyword":"搜索关键词-10","count":951,"range":13,"status":0},{"index":12,"keyword":"搜索关键词-11","count":946,"range":72,"status":1},{"index":13,"keyword":"搜索关键词-12","count":381,"range":13,"status":0},{"index":14,"keyword":"搜索关键词-13","count":150,"range":30,"status":0},{"index":15,"keyword":"搜索关键词-14","count":930,"range":17,"status":1},{"index":16,"keyword":"搜索关键词-15","count":452,"range":9,"status":0},{"index":17,"keyword":"搜索关键词-16","count":668,"range":25,"status":0},{"index":18,"keyword":"搜索关键词-17","count":455,"range":30,"status":1},{"index":19,"keyword":"搜索关键词-18","count":930,"range":44,"status":0},{"index":20,"keyword":"搜索关键词-19","count":674,"range":22,"status":1},{"index":21,"keyword":"搜索关键词-20","count":685,"range":63,"status":0},{"index":22,"keyword":"搜索关键词-21","count":836,"range":10,"status":0},{"index":23,"keyword":"搜索关键词-22","count":354,"range":43,"status":1},{"index":24,"keyword":"搜索关键词-23","count":989,"range":25,"status":0},{"index":25,"keyword":"搜索关键词-24","count":350,"range":91,"status":0},{"index":26,"keyword":"搜索关键词-25","count":361,"range":69,"status":0},{"index":27,"keyword":"搜索关键词-26","count":331,"range":28,"status":0},{"index":28,"keyword":"搜索关键词-27","count":549,"range":92,"status":0},{"index":29,"keyword":"搜索关键词-28","count":210,"range":44,"status":0},{"index":30,"keyword":"搜索关键词-29","count":625,"range":37,"status":1},{"index":31,"keyword":"搜索关键词-30","count":8,"range":33,"status":1},{"index":32,"keyword":"搜索关键词-31","count":165,"range":27,"status":0},{"index":33,"keyword":"搜索关键词-32","count":258,"range":44,"status":1},{"index":34,"keyword":"搜索关键词-33","count":465,"range":97,"status":0},{"index":35,"keyword":"搜索关键词-34","count":621,"range":8,"status":0},{"index":36,"keyword":"搜索关键词-35","count":17,"range":69,"status":1},{"index":37,"keyword":"搜索关键词-36","count":373,"range":50,"status":0},{"index":38,"keyword":"搜索关键词-37","count":78,"range":15,"status":0},{"index":39,"keyword":"搜索关键词-38","count":996,"range":63,"status":0},{"index":40,"keyword":"搜索关键词-39","count":71,"range":34,"status":1},{"index":41,"keyword":"搜索关键词-40","count":799,"range":25,"status":1},{"index":42,"keyword":"搜索关键词-41","count":706,"range":45,"status":0},{"index":43,"keyword":"搜索关键词-42","count":460,"range":80,"status":1},{"index":44,"keyword":"搜索关键词-43","count":46,"range":84,"status":1},{"index":45,"keyword":"搜索关键词-44","count":584,"range":6,"status":1},{"index":46,"keyword":"搜索关键词-45","count":397,"range":98,"status":0},{"index":47,"keyword":"搜索关键词-46","count":904,"range":60,"status":0},{"index":48,"keyword":"搜索关键词-47","count":973,"range":98,"status":0},{"index":49,"keyword":"搜索关键词-48","count":965,"range":64,"status":1},{"index":50,"keyword":"搜索关键词-49","count":929,"range":55,"status":1}],"offlineData":[{"name":"Stores 0","cvr":0.4},{"name":"Stores 1","cvr":0.6},{"name":"Stores 2","cvr":0.4},{"name":"Stores 3","cvr":0.1},{"name":"Stores 4","cvr":0.9},{"name":"Stores 5","cvr":0.7},{"name":"Stores 6","cvr":0.8},{"name":"Stores 7","cvr":0.2},{"name":"Stores 8","cvr":0.8},{"name":"Stores 9","cvr":0.1}],"offlineChartData":[{"x":1559270880372,"y1":84,"y2":60},{"x":1559272680372,"y1":26,"y2":90},{"x":1559274480372,"y1":71,"y2":90},{"x":1559276280372,"y1":16,"y2":49},{"x":1559278080372,"y1":72,"y2":18},{"x":1559279880372,"y1":60,"y2":100},{"x":1559281680372,"y1":28,"y2":49},{"x":1559283480372,"y1":52,"y2":30},{"x":1559285280372,"y1":86,"y2":53},{"x":1559287080372,"y1":109,"y2":46},{"x":1559288880372,"y1":79,"y2":10},{"x":1559290680372,"y1":51,"y2":31},{"x":1559292480372,"y1":76,"y2":37},{"x":1559294280372,"y1":66,"y2":101},{"x":1559296080372,"y1":35,"y2":17},{"x":1559297880372,"y1":22,"y2":64},{"x":1559299680372,"y1":98,"y2":24},{"x":1559301480372,"y1":47,"y2":93},{"x":1559303280372,"y1":27,"y2":85},{"x":1559305080372,"y1":96,"y2":44}],"salesTypeData":[{"x":"家用电器","y":4544},{"x":"食用酒水","y":3321},{"x":"个护健康","y":3113},{"x":"服饰箱包","y":2341},{"x":"母婴产品","y":1231},{"x":"其他","y":1231}],"salesTypeDataOnline":[{"x":"家用电器","y":244},{"x":"食用酒水","y":321},{"x":"个护健康","y":311},{"x":"服饰箱包","y":41},{"x":"母婴产品","y":121},{"x":"其他","y":111}],"salesTypeDataOffline":[{"x":"家用电器","y":99},{"x":"食用酒水","y":188},{"x":"个护健康","y":344},{"x":"服饰箱包","y":255},{"x":"其他","y":65}],"radarData":[{"name":"个人","label":"引用","value":10},{"name":"个人","label":"口碑","value":8},{"name":"个人","label":"产量","value":4},{"name":"个人","label":"贡献","value":5},{"name":"个人","label":"热度","value":7},{"name":"团队","label":"引用","value":3},{"name":"团队","label":"口碑","value":9},{"name":"团队","label":"产量","value":6},{"name":"团队","label":"贡献","value":3},{"name":"团队","label":"热度","value":1},{"name":"部门","label":"引用","value":4},{"name":"部门","label":"口碑","value":1},{"name":"部门","label":"产量","value":6},{"name":"部门","label":"贡献","value":5},{"name":"部门","label":"热度","value":7}]};
    let dq_date = moment(new Date()).format('HH:mm:ss');
    let greeter = "早上好";
    if(dq_date>="06:00:00" && dq_date<"12:00:00"){
      greeter = "早上好";
    }else if (dq_date>="12:00:00" && dq_date<"19:00:00"){
      greeter = "下午好";
    }else{
      greeter = "晚上好";
    }
    ///////////////////////
    const pageHeaderContent = currentUser && Object.keys(currentUser).length ? (
      <div className={styles.pageHeaderContent}>
        <div className={styles.avatar}>
          <Avatar size="large" src={currentUser.image} />
        </div>
        <div className={styles.content}>
          <div className={styles.contentTitle}>
              {greeter}，
            {currentUser.username}
              ，祝你开心每一天！
          </div>
          <div>
            {currentUser.memo?currentUser.memo:"星空不问赶路人，岁月不负有心人"} 
          </div>
        </div>
      </div>
    ) : null;

    const extraContent = (
      <div className={styles.extraContent}>
        {/* <div className={styles.statItem}>
          <p>项目数</p>
          <p>56</p>
        </div> */}
        <div className={styles.statItem}>
          <p>团队名称</p>
          <p>{currentUser.teamname}</p>
        </div>
        <div className={styles.statItem}>
          <p>团队内排名</p>
          <p>
            8<span> / 24</span>
          </p>
        </div>
        <div className={styles.statItem}>
          <p>项目访问</p>
          <p>2,223</p>
        </div>
      </div>
    );

    return (
      <PageHeaderWrapper
        loading={currentUserLoading}
        content={pageHeaderContent}
        extraContent={extraContent}
      >
        <Row gutter={24}>
          <Col xl={16} lg={24} md={24} sm={24} xs={24}>
            <Card
              className={styles.projectList}
              style={{ marginBottom: 24 }}
              title="进行中的项目"
              bordered={false}
              extra={<Link to="/">全部项目</Link>}
              loading={projectLoading}
              bodyStyle={{ padding: 0 }}
            >
              {notice.map(item => (
                <Card.Grid className={styles.projectGrid} key={item.id}>
                  <Card bodyStyle={{ padding: 0 }} bordered={false}>
                    <Card.Meta
                      title={
                        <div className={styles.cardTitle}>
                          <Avatar size="small" src={item.logo} />
                          <Link to={item.href}>{item.title}</Link>
                        </div>
                      }
                      description={item.description}
                    />
                    <div className={styles.projectItemContent}>
                      <Link to={item.memberLink}>{item.member || ''}</Link>
                      {item.updatedAt && (
                        <span className={styles.datetime} title={item.updatedAt}>
                          {moment(item.updatedAt).fromNow()}
                        </span>
                      )}
                    </div>
                  </Card>
                </Card.Grid>
              ))}
            </Card>
            <Card
              bodyStyle={{ padding: 0 }}
              bordered={false}
              className={styles.activeCard}
              title="动态"
              loading={activitiesLoading}
            >
              <List loading={activitiesLoading} size="large">
                <div className={styles.activitiesList}>{this.renderActivities()}</div>
              </List>
            </Card>
          </Col>
          <Col xl={8} lg={24} md={24} sm={24} xs={24}>
            <Card
              style={{ marginBottom: 24 }}
              title="快速开始 / 便捷导航"
              bordered={false}
              bodyStyle={{ padding: 0 }}
            >
              <EditableLinkGroup onAdd={() => {}} links={links} linkElement={Link} />
            </Card>
            <Card
              style={{ marginBottom: 24 }}
              bordered={false}
              title="XX 指数"
              loading={radarData.length === 0}
            >
              <div className={styles.chart}>
                <Radar hasLegend height={343} data={radarData} />
              </div>
            </Card>
            <Card
              bodyStyle={{ paddingTop: 12, paddingBottom: 12 }}
              bordered={false}
              title="团队"
              loading={projectLoading}
            >
              <div className={styles.members}>
                <Row gutter={48}>
                  {notice.map(item => (
                    <Col span={12} key={`members-item-${item.id}`}>
                      <Link to={item.href}>
                        <Avatar src={item.logo} size="small" />
                        <span className={styles.member}>{item.member}</span>
                      </Link>
                    </Col>
                  ))}
                </Row>
              </div>
            </Card>
          </Col>
        </Row>
      </PageHeaderWrapper>
    );
  }
}

export default Workplace;
