import React from 'react';
import { connect } from 'dva';
import Exception from '@/components/Exception';
import RenderAuthorized from '@/components/Authorized';
import { getAuthority } from '@/utils/authority';
import Redirect from 'umi/redirect';
import Link from 'umi/link';

const Authority = getAuthority();
const Authorized = RenderAuthorized(Authority);

const noMatch = (
  <Exception
    type="403"
    desc="抱歉，你无权访问该页面"
    linkElement={Link}
    backText="返回首页"
  />
);

export default connect(({ login }) => ({ login }))(({ login, children }) => (
  login.status !== 'ok'
    ? <Redirect to="/user/login" />
    : (
      <Authorized authority={children.props.route.authority} noMatch={noMatch}>
        {children}
      </Authorized>
    )
));
