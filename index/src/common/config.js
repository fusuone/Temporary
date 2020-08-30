/**
 * 配置
 */
import React, { Fragment } from 'react';
import { Icon } from 'antd';

const appName = '好业绩';
const copyright = <Fragment>Copyright <Icon type="copyright" /> 2018 广州壹软网络科技有限公司</Fragment>;

export default {
  appName,
  platform: 'web_bg',

  copyright,
  basicTitle: `${appName}管理`,

  defaultPageSize: 15,

  globalFooterLinks: [{
    key: '帮助',
    title: '帮助',
    href: 'http://www.qzooe.com/',
    blankTarget: true
  }, {
    key: '隐私',
    title: '隐私',
    href: 'http://www.qzooe.com/',
    blankTarget: true
  }, {
    key: '条款',
    title: '条款',
    href: 'http://www.qzooe.com/',
    blankTarget: true
  }],

  workerMaps: {
    '0': '收胚员',
    '1': '司机',
    '2': '打卷员',
    '3': '仓管员',
    '4': '送货员',
    '100': '全部'
  }
};
