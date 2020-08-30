import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'all',
    tab: '全部'
  },
  {
    key: 'awaiting-examine',
    tab: '待审核'
  },
  {
    key: 'audit-refusal',
    tab: '审核拒绝'
  },
  {
    key: 'awaiting-account',
    tab: '带到账'
  },
  {
    key: 'arrived-account',
    tab: '已到账'
  },
  {
    key: 'failure-pay',
    tab: '代付失败'
  },
  {
    key: 'bank-refund',
    tab: '银行退票'
  }
];

export default class WithdrawCash extends React.PureComponent {
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
