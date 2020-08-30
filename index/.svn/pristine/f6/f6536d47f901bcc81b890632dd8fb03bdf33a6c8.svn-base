import React from 'react';
import router from 'umi/router';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';

const tabList = [
  {
    key: 'classification-management',
    tab: '商品分类管理'
  },
  {
    key: 'brand-management',
    tab: '商品品牌管理'
  },
  {
    key: 'list-management',
    tab: '商品列表管理'
  },
  {
    key: 'repertory-management',
    tab: '库存管理'
  },
  {
    key: 'freight-management',
    tab: '运费管理'
  },
];

export default class Goods extends React.PureComponent {
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
