import React from 'react';
import { Layout } from 'antd';
import GlobalFooter from '@/components/GlobalFooter';
import config from '@/common/config';

const { Footer } = Layout;
const FooterView = () => (
  <Footer style={{ padding: 0 }}>
    <GlobalFooter
      copyright={config.copyright}
      links={config.globalFooterLinks}
    />
  </Footer>
);
export default FooterView;
