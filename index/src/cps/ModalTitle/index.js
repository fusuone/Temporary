import React from 'react';
import { Button } from 'antd';
import styles from './index.less';

export default ({ title, onClose = () => null, onReload = () => null }) => (
  <div className={styles.container}>
    <div className={styles.title}>{title}</div>
    <div className={styles.operator}>
      <Button icon="reload" onClick={onReload} />
      <Button icon="close" onClick={onClose} />
    </div>
  </div>
);
