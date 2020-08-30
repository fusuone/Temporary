# 好业绩后台管理
- 基于ant-design-pro：https://github.com/ant-design/ant-design-pro
- 96f92d4 -> 5f6e69a(2018-10-18)

## 使用
```bash
$ npm install
$ npm start
```

### 使用 docker
 ```bash
// dev 
$ npm run docker:dev
 // build 
$ npm run docker:build
 // production dev 
$ npm run docker-prod:dev
 // production build 
$ npm run docker-prod:build
```

## svn ignore
  ```
  node_modules
  .temp
  src/page/.umi
  src/common/devInfo.js  // checkout 复制文件"暂存/devInfo.js"
  ```

## 支持环境
现代浏览器及 IE11。

## 计划
  - [√] 未登录重定向到登录页 https://github.com/ant-design/ant-design-pro/issues/2157

## bug
  - [√] 兼容 ie 升级到5f6e69a解决
        其他：https://ant.design/docs/react/getting-started-cn#%E5%85%BC%E5%AE%B9%E6%80%A7

## bug(框架)
  - [√] 改 less 文件，编译速度非常慢，原因：https://github.com/ant-design/ant-design-pro/issues/2947

## 注意
  1.优化打包，一些 js 库走 cdn，在 config/config.js/externals、document.ejs 查看，
    需要和 package.json 中的版本对应，由于 package.json 中一些包使用"^"拉取的版本，
    所以具体版本要在 package-lock.json 查看，为了避免无法预料的错误，尽量使用对应版本。
    参考：https://github.com/ant-design/ant-design-pro/issues/2180#issuecomment-418648150
  2.Table 组件的 rowKey="id" 要唯一，要不可能会出现出乎意料的错误