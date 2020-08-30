import React from 'react';
// import { Upload, Icon, Modal } from 'antd';
import styles from './Decoration.less';
import Api from '@/common/api';
// /////////////////////////////////////////
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker,Popconfirm, Radio, Switch, message, Upload, Form, Row, Col, Input } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;



function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class Decoration extends React.PureComponent {
  constructor(props){
    super(props);
    this.state = {
      previewVisible: false,
      previewImage: '',
      fileList: [
        // {
        //   uid: '-1',
        //   // name: 'xxx.png',
        //   // status: 'done',
        //   url: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
        // },
      ],
      fileImage: "",
      start_time: "",
      end_time: "",


      getLoading: false,
          RealNameDate : [],
          RealName_billno: "",
          modalVisible: false, //modal
          status: -10,
          modalData: {}, 
          ////////////////////
          detailModalVisible: false, //详情页面显示状态
          detailModalData: {},
          total: '',
          reqParams: {
            page: 1,
            pagesize: 15,
            begin: '',
            end: '',
          }




    };
    ////////////
    this.columns = [
      {
        title: '图片',
        width: 250,
        dataIndex: 'ad_code',
        key: 'ad_code',
        fixed: 'left',
        render: (text, record) =>{
          return(
            <img style={{width:"200px",height:"100px"}} src={record.ad_code}></img>
          );
        }
      },
      {
        title: '是否关闭广告',
        dataIndex: 'enabled',
        key: 'enabled',
        width: 120,
        render: (text, record) =>{
          let txt = "";
          if(record.enabled==0){
            text="已关闭";
          }else if(record.enabled==1){
            text="已开启";
          }
          return text;
        }
      },
      {
        title: '图片类型',
        dataIndex: 'img_type',
        key: 'img_type',
        width: 120,
        render: (text, record) =>{
          let txt = "";
          if(record.img_type==0){
            text="店铺";
          }else if(record.img_type==1){
            text="商品";
          }
          return text;
        }
      },
      {
        title: '广告链接地址',
        width: 250,
        dataIndex: 'ad_link',
        key: 'ad_link',
      },
      {
          title: '广告开始时间',
          dataIndex: 'start_time',
          key: 'start_time',
          width: 200,
      },
      {
        title: '广告开始时间',
        dataIndex: 'end_time',
        key: 'end_time',
        width: 200,
      },
      {
        title: '广告联系人',
        dataIndex: 'link_man', 
        key: 'link_man',
        width: 150,
      },
      {
        title: '广告联系人邮箱',
        dataIndex: 'link_email',
        key: 'link_email',
        width: 250,
      },
      {
        title: '广告联系人电话',
        dataIndex: 'link_phone',
        key: 'link_phone',
        width: 200,
      },
      {
        title: '广告点击数',
        dataIndex: 'click_count',
        key: 'click_count',
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) =>{
          this.setState({
            RealName_billno: record.billno
          });
          //操作菜单
          let OperateMenu = (
            <Menu>
              <Menu.Item>
                <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,0)}>关闭</a>
                <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record,1)}>开启</a>
              </Menu.Item>
            </Menu>
          );
          return(
            <div>
            <a　onClick={(ev) => {this.detailModal(ev,record)}}>详请</a>
            <Divider type="vertical" />
            <Dropdown overlay={OperateMenu}>
              <span className="ant-dropdown-link" href="#">
                更多 <Icon type="down" />
              </span>
            </Dropdown>
          </div>
          );
        }     
      },
    ];
  }


  //操作提示
  confirm = (ev,record,d) => {
    let calltxt = "";
    switch(d){
      case 0: 
        calltxt = "关闭";
        break;
      case 1: 
        calltxt = "开启";
        break;
    }
    Modal.confirm({
      title: `广告显示状态设置为 ${calltxt}`,
      content: `你确定要把广告链接地址为：“ ${record.ad_link} ”的广告显示状态设置为 “${calltxt}” 吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.confirmHandleOk(record,d),
      onCancel: this.confirmHandleCencle,
    });
  }

  confirmHandleOk = (record,d) => {
    http({
      method: 'post',
      api: 'updateshopadstatus',
      params: { 
        enabled: d,
        c_billno: record.billno,
      },
    }).then((result) => {
      const { status, msg, data } = result;
      if (status == '0') {
        message.info('修改成功');
        this.getList();
      } else {
        message.info(msg);
      }
    }).catch(() => {
      message.info('操作失败');
    });
  }


  handleCancel = async file => {
    this.setState({ previewVisible: false });
  }

  handlePreview = async file => {
    if (!file.url && !file.preview) {
      file.preview = await getBase64(file.originFileObj);
    }

    this.setState({
      previewImage: file.url || file.preview,
      previewVisible: true,
    });
  };

  handleChange = ({ fileList }) => {
    setTimeout(()=>{
      if(fileList[0]){
        this.setState({
          fileImage: fileList[0].response.data.source,
        })
      }
    },2000);
    let img = "";
   
    this.setState({ 
      fileList,
    });
  } 


  beforeUpload = (file, fileList) => {
  }





  // /////////////////////////////////////////////////////////////
  componentDidMount() {
      this.getList();
  };

    //设置modalData
    getModalData = (key) =>{
      let key1 = key - 1;
      let modalData1 = this.state.RealNameDate[key1]
      this.setState({
        modalData: modalData1
      });
    }

    //获取审核列表数据
    getList = () => {
      let { currentUser } = this.props;
      let { reqParams } = this.state;
      http({
        method: 'post',
        api: 'getbannerinfo',
        params: reqParams,
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            RealNameDate: data.list,
            getLoading: false,
            total: data.total,
          });
        } else {
          message.warn(msg);
          this.setState({
            RealNameDate: [],
            getLoading: false
          });
        }
      }).catch(() => {
        this.setState({ getLoading: false });
      });
    }


    //modal
    addShowModal = () => {
      this.setState({
        modalVisible: true,
      });
    };
  
    //添加
    addHandleOk = e => {
      const { fileImage } = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
      if (!err) {
        if(fileImage==""){
          message.info("请点击添加图片");
          return ;
        }
        http({
          method: 'post',
          api: 'addshopad',
          params: { 
            ad_bno: values.ad_bno,
            ad_code: fileImage,
            ad_link: values.ad_link,
            end_time: values.end_time, 
            start_time: values.start_time,
            img_type: values.img_type,
            link_email: values.link_email,
            link_man: values.link_man,
            link_phone: values.link_phone, 
            link_man: values.link_man,
          },
        }).then((result) => {
          const { status, msg, data } = result;
          if (status == '0') {
            message.info('修改成功');
            this.getList();
            this.addHandleCancel();
          } else {
            message.info(msg);
          }
        }).catch(() => {
          message.info('操作失败');
        });
         
       }
     });
    };
  
    addHandleCancel = e => {
      this.props.form.resetFields();
      this.setState({
        modalVisible: false,
        fileImage: "",
        end_time: "",
        star_time: "",
      });
    };
    //未通过
    notAdopted = (e) =>{
      this.setState({
        status: -1
      }),this.setRealNameList();
    }
    //////////////


    ///未通过原因 

    onChange = (value) => {
    }

    onBlur = () => {
    }

    onFocus = () => {
    }

    onSearch = (val) => {
    }
    ///
    

    //更新审核列表数据
    setRealNameList = () => {
      if(this.state.status==-10) {return;}
      http({
        method: 'post',
        api: 'set_realname',
        params: {
          userno: this.props.currentUser.userno,
          billno: this.state.modalData["billno"],
          status: this.state.status
        }
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({ 
            getLoading: false,
            modalVisible: false,
            status: -10
          }),this.getList();
        } else {
          message.warn(msg);
          this.setState({
            getLoading: false,
            status: -10
          });
        }
      }).catch(() => {
        this.setState({ getLoading: false, status: -10 });
      });
    }


    /////////////////////////////////////////////////
    //详情modal
    detailModal  =(ev,data) =>{
        this.setState({
            detailModalData: data,
            detailModalVisible: true
        });
    }

    detailHandleCancel = (e) => {
        this.setState({
            detailModalVisible: false
        });
    }

    getStatus = (d) => {
        let status = ""
        if(d == 0){
          status = "未提交";
        }else if(d == 1){
          status = "已提交(审核中)";
        }else if (d == 2){
            status = "通过";
        }else if (d == 3){
            status = "不通过";
        }else if(d == -1){
            status = "无效";
        }
        return status;
    }

    //分页
    getPaginationdata = (page, pageSize) => {
        this.state.reqParams.page=page;
        this.getList();
    }
    //日期选择
    onChangeRangePicker = (dates,dateStrings) =>　{
        this.state.reqParams.begin = dateStrings[0];
        this.state.reqParams.end = dateStrings[1];
        this.getList();
    }


    getEnabled = (d) => {
      let txt = "";
      if(d==0){
        txt="已关闭";
      }else if(d==1){
        txt = "已开始";
      }
      return txt;
    }

    //选择广告开始时间
    onChange_start_time = (date, dateString) => {
      this.setState({
        start_time: dateString
      })
      this.props.form.setFieldsValue({
        start_time : dateString
      });
    }

    //选择广告结束时间
    onChange_end_time = (date, dateString) => {
      this.setState({
        end_time: dateString
      })
      this.props.form.setFieldsValue({
        end_time : dateString
      });
    }
  // ////////////////////////////////////////////////////////////

  render() {
    const { previewVisible, previewImage, fileList, getLoading, detailModalData } = this.state;
    const uploadButton = (
      <div>
        <Icon type="plus" />
        <div className="ant-upload-text">添加</div>
      </div>
    );
    const { getFieldDecorator } = this.props.form;  
    //////
    // const { getLoading, detailModalData } = this.state;
    const { Option } = Select;   
    return (
      <div className={styles.clearfix}>
      {/* <Upload 
        name = "file"
        action={Api.uploadimg} 
        listType="picture-card"
        fileList={fileList}
        onPreview={this.handlePreview}
        onChange={this.handleChange}
      >
        {fileList.length >= 1 ? null : uploadButton}
      </Upload>
      <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
        <img className={styles.previewImage} alt="example" style={{ width: '100%' }} src={previewImage} />
      </Modal> */}

      {/* ////////////////////////////////////////////////////////////// */}
     <PageHeaderWrapper>
            <div>
              <div  className={styles.example}>
                <Spin spinning={ getLoading } />
              </div>
              <Card>
                <RangePicker style={{margin: "0 0 15px 0"}} onChange={this.onChangeRangePicker} />
                <Button  onClick={this.addShowModal} style={{marginLeft:"20px"}} type="primary" >添加</Button>
                <Table 
                    onRow={record => {
                    return {
                        onClick: event => {

                        this.setState({
                            modalData: record,
                            RealName_billno: record.billno
                        });
                        }, // 点击行
                    };
                    }}
                    columns={this.columns} 
                    dataSource={this.state.RealNameDate} 
                    scroll={{ x: 2000, y: 500 }} 
                    pagination={{
                        pageSize:this.state.reqParams.pagesize,
                        total: this.state.total,
                        onChange: this.getPaginationdata,
                    }}
              />
              </Card>
              {/* modal */}
              <div>
                <Modal
                  title="广告添加"
                  visible={this.state.modalVisible}
                  // onOk={this.handleOk}
                  onCancel={this.addHandleCancel}
                //   width={1000}
                  maskClosable={false}
                  footer={[
                    <Button key="back" onClick={this.addHandleCancel}>
                      取消
                    </Button>,
                    // <Button type="danger" onClick={this.notAdopted}>
                    //   未通过
                    // </Button>,
                    <Button key="submit" type="primary" onClick={this.addHandleOk}>
                      添加
                    </Button>,
                  ]}
                >
                  <Form layout="vertical">
                    <Row gutter={24}>
                      <Col>
                        <Form.Item label="图片：">
                          <Upload 
                            name = "file"
                            action={Api.uploadimg} 
                            listType="picture-card"
                            fileList={fileList}
                            onPreview={this.handlePreview}
                            onChange={this.handleChange}
                          >
                            {fileList.length >= 1 ? null : uploadButton}
                          </Upload>
                          <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
                            <img className={styles.previewImage} alt="example" style={{ width: '100%' }} src={previewImage} />
                          </Modal> 
                        </Form.Item>
                      </Col>
                      <Col>
                      <Form.Item label="图片类型：">
                          {getFieldDecorator('img_type', {
                          rules: [{ required: true, message: '请输入选择图片类型' }],
                          // initialValue: modifyModalData.express_name
                          })(
                            <Radio.Group>
                              <Radio value={0}>店铺</Radio>
                              <Radio value={1}>商品</Radio>
                            </Radio.Group>
                          )}
                      </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告唯一识别码(billno)：">
                            {getFieldDecorator('ad_bno', {
                            rules: [{ required: true, message: '请输入广告唯一识别码' }],
                            // initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入广告唯一识别码" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告链接：">
                            {getFieldDecorator('ad_link', {
                            rules: [{ required: true, message: '请输入广告链接' }],
                            // initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入广告链接" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告开始时间：">
                            {getFieldDecorator('start_time', {
                            rules: [{ required: true, message: '请选择广告开始时间' }],
                            initialValue:this.state.start_time,
                            })(
                              <div>
                              <Input hidden="hidden" value={this.state.start_time}/>
                              <DatePicker
                                style={{width:"100%",float:"left"}}
                                onChange={this.onChange_start_time} 
                                placeholder="请选择广告开始日期"
                                showTime
                              />
                            </div> 
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告结束时间：">
                            {getFieldDecorator('end_time', {
                            rules: [{ required: true, message: '请选择广告结束时间' }],
                            initialValue: this.state.end_time,
                            })(
                              <div>
                                <Input hidden="hidden" defaultValue value={this.state.end_time}/>
                                <DatePicker
                                  style={{width:"100%",float:"left"}}
                                  onChange={this.onChange_end_time} 
                                  placeholder="请选择广告结束时间"
                                  showTime
                                />
                              </div> 
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告联系人：">
                            {getFieldDecorator('link_man', {
                            rules: [{ required: true, message: '请输入广告联系人' }],
                            // initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入广告联系人" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告联系人邮箱：">
                            {getFieldDecorator('link_email', {
                            rules: [{ required: true, message: '请输入广告联系人邮箱' }],
                            // initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入广告联系人邮箱" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="广告联系人电话：">
                            {getFieldDecorator('link_phone', {
                            rules: [{ required: true, message: '请输入广告联系人电话' }],
                            // initialValue: modifyModalData.express_no
                            })(
                            <Input placeholder="请输入广告联系人电话" />
                            )}
                        </Form.Item>
                      </Col>
                    </Row>
                  </Form>
                </Modal>
              </div>
              {/* 详情 */}
              <Modal
                  title="客户详情"
                  visible={this.state.detailModalVisible}
                  onCancel={this.detailHandleCancel}
                //   width={1000}
                  maskClosable={false}
                  footer={[
                    <Button key="back" onClick={this.detailHandleCancel}>
                      关闭
                    </Button>,
                  ]}
                >
                    <div className={styles.detailContainer}>
                    <p><span>图片：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={detailModalData.ad_code} alt="img" /></span></p>
                    <p><span>图片链接地址：</span><span className="content">{detailModalData.ad_link}</span></p>
                    <p><span>广告内容, 文字或图片地址：</span><span className="content">{detailModalData.ad_code}</span></p>
                    <p><span>广告开始时间：</span><span className="content">{detailModalData.star_time}</span></p>
                    <p><span>广告结束时间：</span><span className="content">{detailModalData.end_time}</span></p>
                    <p><span>广告联系人：</span><span className="content">{detailModalData.link_man}</span></p>
                    <p><span>广告联系人邮箱：</span><span className="content">{detailModalData.link_email}</span></p>
                    <p><span>广告联系人电话：</span><span className="content">{detailModalData.link_tel}</span></p>
                    <p><span>广告点击数量：</span><span className="content">{detailModalData.lick_count}</span></p>
                    <p><span>广告是否关闭：</span><span className="content">{this.getEnabled(detailModalData.enabled)}</span></p>
                    </div>
                </Modal>
            </div>
          </PageHeaderWrapper>









    </div>
    );
  }
}

export default Decoration;
