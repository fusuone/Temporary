import React, { PureComponent } from 'react';
import { Row, Col } from 'antd';

import TableList from './TableList';
import AddPanel from './AddPanel';

class Output extends PureComponent {
  getTableList = () => {
    return this.listContext.getListData();
  }

  resetTableList = () => {
    return this.listContext.resetInitial();
  }

  render() {
    return (
      <Row gutter={24}>
        <Col style={{ marginBottom: '24px' }}>
          <AddPanel
            getContext={c => this.addContext = c}
            getTableList={this.getTableList}
            resetTableList={this.resetTableList}
          />
        </Col>
        <Col style={{ marginBottom: '24px' }}>
          <TableList
            getContext={c => this.listContext = c}
          />
        </Col>
      </Row>
    );
  }
}

export default Output;
