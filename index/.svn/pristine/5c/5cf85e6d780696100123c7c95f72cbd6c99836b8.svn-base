import React from 'react';
import Link from 'umi/link';
import DocumentTitle from 'react-document-title';
import GlobalFooter from '@/components/GlobalFooter';
// import SelectLang from '@/components/SelectLang';
import styles from './UserLayout.less';
import logo from '../assets/logo.png';
import config from '@/common/config';

class UserLayout extends React.PureComponent {
  getPageTitle = () => {
    // const { routerData, location } = this.props;
    // const { pathname } = location;
    // let title = '好业绩';
    // if (routerData[pathname] && routerData[pathname].name) {
    //   title = `${routerData[pathname].name} - 好业绩`;
    // }
    // return title;
    return '好业绩';
  }

  render() {
    const { children } = this.props;
    return (
      <DocumentTitle title={this.getPageTitle()}>
        <div className={styles.container}>
          <div className={styles.lang}>
            {/* <SelectLang /> */}
          </div>
          <div className={styles.content}>
            <div className={styles.top}>
              <div className={styles.header}>
                <Link to="/">
                  <img alt="logo" className={styles.logo} src={logo} />
                  <span className={styles.title}>好业绩后台</span>
                </Link>
              </div>
              <div className={styles.desc} />
            </div>
            {children}
          </div>
          <GlobalFooter
            copyright={config.copyright}
            links={config.globalFooterLinks}
          />
        </div>
      </DocumentTitle>
    );
  }
}

export default UserLayout;
