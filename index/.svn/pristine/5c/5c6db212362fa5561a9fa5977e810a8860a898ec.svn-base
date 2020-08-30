import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'home-page-picture',
    tab: '首页图片'
  },
  {
    key: 'preferential-activity',
    tab: '优惠活动'
  },
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
