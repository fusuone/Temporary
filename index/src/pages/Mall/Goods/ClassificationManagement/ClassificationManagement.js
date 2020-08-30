import React  from 'react';
import { Modal, Button, Card, Form, Input, Upload, Icon } from 'antd';



class ClassificationManagement extends React.PureComponent {
  constructor(props){
    super(props);
    this.state = {
      ModalText: 'Content of the modal',
      visible: false,
      confirmLoading: false,
      //图片
      previewVisible: false,
      previewImage: '',
      fileList: []
      // fileList: [{
      //   uid: '-1',
      //   name: 'xxx.png',
      //   status: 'done',
      //   url: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
      // }],
    };
  }


  showModal = () => {
    this.setState({
      visible: true,
    });
  }

  handleOk = () => {
    this.setState({
      ModalText: 'The modal will be closed after two seconds',
      confirmLoading: true,
    });
    setTimeout(() => {
      this.setState({
        visible: false,
        confirmLoading: false,
      });
    }, 2000);
  }

  ModelhandleCancel = () => {
    this.setState({
      visible: false,
    });
  }


  
    
  getPaginationdata=(page, pageSize)=>{
  }

  //图片上传
  handleCancel = () => this.setState({ previewVisible: false })

  handlePreview = (file) => {
    this.setState({
      previewImage: file.url || file.thumbUrl,
      previewVisible: true,
    });
  }

  handleChange = ({ fileList }) => this.setState({ fileList })





  render() {
    const { visible, confirmLoading, ModalText } = this.state;
    //图片
const { previewVisible, previewImage, fileList } = this.state;
const uploadButton = (
  <div>
    <Icon type="plus" />
    <div className="ant-upload-text">Upload</div>
  </div>
);
    return (
      <div>
        <Card>
          <Button type="primary" onClick={this.showModal}>
            添加新分类
          </Button>
          <Modal title="添加产品分类"
            visible={visible}
            onOk={this.handleOk}
            confirmLoading={confirmLoading}
            onCancel={this.ModelhandleCancel}
            maskClosable={false}
          >
            <p style={{textAlign:"center"}}>
            <Form layout="inline">
              <Form.Item
                label="分类名称"
              >
                <Input placeholder="请输入分类名称" />
              </Form.Item>
              <Form.Item
                label="产品类别"
              >
                <Input placeholder="请选择类别" />
              </Form.Item>
              <Form.Item
                label="图片"
              >
                <Card style={{border:0}}>
                  <div className="clearfix">
                    <Upload
                      action="//jsonplaceholder.typicode.com/posts/"
                      listType="picture-card"
                      fileList={fileList}
                      onPreview={this.handlePreview}
                      onChange={this.handleChange}
                    >
                      {fileList.length >= 1 ? null : uploadButton}
                    </Upload>
                    <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
                      <img alt="example" style={{ width: '100%' }} src={previewImage} />
                    </Modal>
                  </div>
                </Card>
              </Form.Item>
            </Form>

            </p>
          </Modal>
        </Card>
      </div>
    );
  }
}

export default ClassificationManagement;