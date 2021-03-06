import React, { PureComponent } from 'react';

import { Row, Card, Modal, Button, DatePicker } from 'antd'


class MyWorkPlan extends PureComponent{
    constructor(props){
        super(props);
        this.state = {
            visible: false
        }
    }

    //添加计划模态框开始
    showModal = () => {
        this.setState({
            visible: true,
        });
    }
    
    handleOk = (e) => {
    this.setState({
        visible: false,
    });
    }

    handleCancel = (e) => {
    this.setState({
        visible: false,
    });
    }
    //添加计划模态框开始结束

    //
    onChange = (e) => {
    }

    render(){
        return(
            <div>
                 <Card style={{textAlign:"center",border:0}}>
                    {/* //添加计划模态框 */}
                    <div>
                        <Button type="primary" onClick={this.showModal}>
                        添加我的工作计划
                        </Button>
                        <Modal
                            title="添加我的工作计划"
                            visible={this.state.visible}
                            onOk={this.handleOk}
                            onCancel={this.handleCancel}
                        >
                            <DatePicker placeholder="请选择计划时间"/>
                        </Modal>
                    </div>
                </Card>
            </div>
            
        );
    }
}

export default MyWorkPlan;