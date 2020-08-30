import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'customer',
    tab: '客户'
  },
  {
    key: 'art',
    tab: '规格工艺'
  },
  {
    key: 'car',
    tab: '车型车牌'
  },
  {
    key: 'depot',
    tab: '仓库设置'
  },
  {
    key: 'worker',
    tab: '工作人员'
  }
];

export default class Index extends React.PureComponent {
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
