// https://umijs.org/config/
import os from 'os';
import pageRoutes from './router.config';
// import webpackPlugin from './plugin.config'; // 如果使用，修改 less 编译速度非常慢，原因：https://github.com/ant-design/ant-design-pro/issues/2947
import defaultSettings from '../src/defaultSettings';

export default {
  // add for transfer to umi
  plugins: [
    [
      'umi-plugin-react',
      {
        antd: true,
        dva: {
          hmr: true
        },
        targets: {
          ie: 11
        },
        locale: {
          enable: true, // default false
          default: 'zh-CN', // default zh-CN
          baseNavigator: true // default true, when it is true, will use `navigator.language` overwrite default
        },
        // 生产模式不需要按需加载
        dynamicImport: process.env.NODE_ENV === 'production' ? false : {
          loadingComponent: './components/PageLoading/index'
        },
        ...(!process.env.TEST && os.platform() === 'darwin'
          ? {
            dll: {
              include: ['dva', 'dva/router', 'dva/saga', 'dva/fetch'],
              exclude: ['@babel/runtime']
            },
            hardSource: true
          }
          : {})
      }
    ],
    [
      'umi-plugin-ga',
      {
        code: 'UA-72788897-6',
        judge: () => process.env.APP_TYPE === 'site'
      }
    ]
  ],
  targets: {
    ie: 11
  },
  define: {
    APP_TYPE: process.env.APP_TYPE || ''
  },
  // 路由配置
  routes: pageRoutes,
  // Theme for antd
  // https://ant.design/docs/react/customize-theme-cn
  theme: {
    'primary-color': defaultSettings.primaryColor
  },
  externals: {
    '@antv/g2': 'G2',
    '@antv/data-set': 'DataSet',
    'react': 'React',
    'bizcharts': 'BizCharts',
    'react-dom': 'ReactDOM'
  },
  ignoreMomentLocale: true,
  lessLoaderOptions: {
    javascriptEnabled: true
  },
  cssLoaderOptions: {
    modules: true,
    getLocalIdent: (context, localIdentName, localName) => {
      if (
        context.resourcePath.includes('node_modules')
        || context.resourcePath.includes('ant.design.pro.less')
        || context.resourcePath.includes('global.less')
      ) {
        return localName;
      }
      const match = context.resourcePath.match(/src(.*)/);
      if (match && match[1]) {
        const antdProPath = match[1].replace('.less', '');
        const arr = antdProPath
          .split('/')
          .map(a => a.replace(/([A-Z])/g, '-$1'))
          .map(a => a.toLowerCase());
        return `antd-pro${arr.join('-')}-${localName}`.replace(/--/g, '-');
      }
      return localName;
    }
  },
  manifest: {
    name: 'ant-design-pro',
    background_color: '#FFF',
    description: 'An out-of-box UI solution for enterprise applications as a React boilerplate.',
    display: 'standalone',
    start_url: '/index.html',
    icons: [
      {
        src: '/favicon.png',
        sizes: '48x48',
        type: 'image/png'
      }
    ]
  },

  disableRedirectHoist: true,
  // chainWebpack: webpackPlugin,
  cssnano: {
    mergeRules: false
  },

  hash: true, // 给打包文件加上 chunkhash
  history: 'hash',
  publicPath: './'
};
