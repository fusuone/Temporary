import React, { PureComponent } from 'react';
import { Row, Col } from 'antd';

import TableList from './TableList';
import AddPanel from './AddPanel';

class TrackCut extends PureComponent {
  getCurrentCrudeItem = () => {
    return this.addContext.getCurrentCrudeItem();
  }

  fetchTrackList = () => {
    return this.listContext.getList();
  }

  render() {
    return (
      <Row gutter={24}>
        <Col style={{ marginBottom: '24px' }}>
          <AddPanel
            getContext={c => this.addContext = c}
            fetchTrackList={this.fetchTrackList}
          />
        </Col>
        <Col style={{ marginBottom: '24px' }}>
          <TableList
            getContext={c => this.listContext = c}
            getCurrentCrudeItem={this.getCurrentCrudeItem}
          />
        </Col>
      </Row>
    );
  }
}

export default TrackCut;
