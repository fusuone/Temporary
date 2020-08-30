import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'all-orders',
    tab: '全部订单'
  },
  {
    key: 'awaiting-payment',
    tab: '待付款'
  },
  {
    key: 'awaiting-send',
    tab: '待发货'
  },
  {
    key: 'awaiting-shipment',
    tab: '待收货'
  },
  {
    key: 'valuat',
    tab: '待评论'
  },
  // {
  //   key: 'regular-purchase',
  //   tab: '我的常购商品'
  // },
  
  {
    key: 'recycling-bin',
    tab: '订单回收站'
  }
];

export default class Bill extends React.PureComponent {
  handleTabChange = (key) => {
    const { match } = this.props;
    router.push(`${match.url}/${key}`);
  }

  render() {
    const { match, children, location } = this.props;
    return (
      <PageHeaderWrapper
        tabList={tabList}
        tabActiveKey={location.pathname.replace(`${match.path}/`, '')}
        onTabChange={this.handleTabChange}
      >
        {children}
      </PageHeaderWrapper>
    );
  }
}
