import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'cut',
    tab: '码单录入'
  },
  {
    key: 'list',
    tab: '码单明细'
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
