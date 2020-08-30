import React, { PureComponent } from 'react';
import { Table, Card, Divider, Menu, Dropdown, Icon, Spin, Button, Modal, Select, DatePicker, Input, Form, Row, Col, message, Upload, Radio } from 'antd';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { connect } from 'dva';
import http from '@/utils/http';
import Api from '@/common/api';
import styles from './PreferentialActivity.less';
import { imageCompress } from '@/cps/ImagePicker/utils';
import { reduce } from 'zrender/lib/core/util';
const {RangePicker } = DatePicker;
const { Search } = Input;

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
class PreferentialActivity extends PureComponent{
  constructor(props){
    super(props);
    this.state={
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
        userno: "",
        page: 1,
        pagesize: 15,
        begin: '',
        end: '',
        searchkey: '',
      },
      modifyModalData: {},
      modalTitle: "添加",
      // 
      previewVisible: false,
      previewImage: '',
      fileList: [],
      fileImage: [],
      img: "",
      
    }
    this.columns = [
      {
        title: '商品图片',
        width: 130,
        dataIndex: 'image1',
        key: 'image1',
        fixed: 'left',
        render: (text, record) => {
          return(
            <img style={{width:100,height:60}} src={record.image1} />
          );
        }
      },
      {
        title: '商品名称',
        width: 150,
        dataIndex: 'waresname',
        key: 'waresname',
      },
      {
        title: '商品编码',
        dataIndex: 'productno',
        key: 'productno',
        width: 150,
      },
      {
        title: '商品类型',
        dataIndex: 'type',
        key: 'type',
        width: 120,
        render: (text, record) => {
          return(
            <div>
              {record.type==0?"进销的产品":"生产的产品"}
            </div>
          );
        }
      },
      {
        title: '商城在售',
        dataIndex: 'onsale',
        key: 'onsale',
        width: 120,
        render: (text, record) => {
          return(
            <div>
              {record.type==0?"下架不在售":"上架在售"}
            </div>
          );
        }
      },
      {
        title: '库存数',
        dataIndex: 'qty',
        key: 'qty',
        width: 100,
      },
      {
        title: '次品',
        dataIndex: 'qty1',
        key: 'uniqty1t',
        width: 100,
      },
      {
        title: '坏品',
        dataIndex: 'qty2',
        key: 'qty2',
        width: 100,
      },
      {
        title: '其他',
        dataIndex: 'qty3',
        key: 'qty3',
        width: 100,
      },
      {
        title: '单位',
        dataIndex: 'unit',
        key: 'unit',
        width: 100,
      },
      {
        title: '添加时间',
        dataIndex: 'billdate',
        key: 'billdate',
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
                <a className={styles.operrateColor} onClick={(ev) => this.confirm(ev,record)}>删除</a>
                <a className={styles.operrateColor} onClick={(ev) => this.showModal(ev,record)}>编辑</a>
                {/* <a>更多</a> */}
              </Menu.Item>
            </Menu>
          );
          return(
            <div>
            <a　onClick={(ev) => {this.detailModal(ev,record)}}>详请</a>
            <Divider type="vertical" />
            <Dropdown overlay={OperateMenu}>
              <span style={{color:"#1890ff"}} className="ant-dropdown-link" href="#">
                更多 <Icon type="down" />
              </span>
            </Dropdown>
          </div>
          );
        }      
      },
    ];
  }

    
  componentDidMount() {
    this.getAdminInfo();
  };


  //获取管理员信息
  getAdminInfo = ()　=> {
    http({
      method: 'get',
      api: 'getadminbillno',
      params: {
        admin: this.props.currentUser.admin
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
         this.state.reqParams.userno=data[0].billno;
         this.getList();
        
      } else {
        message.warn(msg);
      }
    }).catch(() => {
    });
  }

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
      api: 'getmalldiscount',
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

  //添加数据
  addData = ()=>{
    this.setState({
      modalVisible: true,
      modifyModalData: [],
      modalTitle: "添加",
    });
  }

  //modal
  showModal = (ev,record,d) => {
    let { fileList } = this.state;
    for(let i=1;i<6;i++){
      let a = ""
      switch(i){
        case 1:
          a=record.image1;
          break;
        case 2:
          a = record.image2;
          break;
        case 3: 
          a = record.image3;
          break;
        case 4: 
          a = record.image4;
          break;
        case 5: 
          a = record.image5;
          break;
        case 6: 
          a = record.image6;
          break;
      }
      let b = `image${i}`;
      if(!record[b]==""){
        this.state.fileList[i-1]= {
          uid: i,
          name: `image${i}`,
          status: 'done',
          url: a,
        };
        this.state.fileImage[i-1]=record[b];
      }
      
    }
    this.setState({
      modalVisible: true,
      modifyModalData: record,
      modalTitle: "编辑",
    });
  };

  
  handleOk = e => {
    this.setState({
      status: 2
    }),this.setRealNameList();
  };
  

  //取消添加操作
  handleCancel = e => {
    this.props.form.resetFields();
    this.setState({
      modalVisible: false,
      modifyModalData: {},
      fileImage: [],
      fileList: [],
    });
  };


  //未通过
  notAdopted = (e) =>{
    this.setState({
      status: -1
    }),this.setRealNameList();
  }


  ///未通过原因 
  onChange = (value) => {
  }

  onBlur = () => {
  }

  onFocus = () => {
  }

  onSearch = (val) => {
  }
    

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

  //搜索
  onSearch = (value) => {
    this.state.reqParams.searchkey = value;
    this.getList();
  }

  confirm = (ev,record) => {
    Modal.confirm({
      title: `删除优惠商品`,
      content: `你确定要把商品名称为“${record.waresname}”的优惠商品删除吗？`,
      okText: '确认',
      cancelText: '取消',
      onOk: () => this.setStatus(record),
      onCancel: this.confirmHandleCencle,
    });
  }


  //删除
  setStatus = (record) => {
    http({
      method: 'get',
      api: 'delmallwares',
      params: { 
        billno: record.billno,
      },
    }).then((result) => {
      const { status, msg, data } = result;
      if (status == '0') {
        message.info(msg);
        this.getList();
      } else {
        message.info(msg);
      }
    }).catch(() => {
      message.info('操作失败');
    });
  }


  //提交表单
  handleSubmit = e => {
    let { modifyModalData, fileImage } = this.state;
      e.preventDefault();
      this.props.form.validateFields((err, values) => {
      if (!err) {
        if(fileImage.length==0){
          message.info("请点击添加图片");
          return;
        }
        const data = {
          billno: modifyModalData.billno?modifyModalData.billno:"",
          image1: fileImage[0]?fileImage[0]:"",
          image2: fileImage[1]?fileImage[1]:"",
          image3: fileImage[2]?fileImage[2]:"",
          image4: fileImage[4]?fileImage[4]:"",
          image5: fileImage[5]?fileImage[5]:"",
          image6: fileImage[6]?fileImage[6]:"",
          admin: this.props.currentUser.admin,
          username: this.props.currentUser.username,
          waresname: values.waresname, // 名称
          model: values.model, // 型号
          productno: values.productno, // 编码
          price: values.price, // 单价
          unit: values.unit, // 单位
          description: values.description, // 描述
          waresno: values.waresno, 
          waretype: values.waretype,
          qty: values.qty,
          qty1: values.qty1,
          qty2: values.qty2,
          qty3: values.qty3,
        }
        http({
          method: 'post',
          api: 'setmallware',
          data: {
            ...data,  
          },
        }).then((result) => {
          const { status, msg, data } = result;
          if (status == '0') {
            message.info('修改成功');
            this.handleCancel();
            this.getList();
          } else {
            message.info(msg);
          }
        }).catch(() => {
          message.info('操作失败');
        });
      }
    });
  }


  handleCancelPreview  = async file => {
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

  handleChangePreview = ({ fileList }) => {
    const fileImageArray = [];
    for(let i=0;i<fileList.length;i++){
      let a = "";
      if(fileList[i].response){
        a = fileList[i].response.data.source;
      }else{
        a = fileList[i].url;
      }
      fileImageArray[i] = a;
    }
     setTimeout(()=>{
        this.setState({
          fileImage: fileImageArray,
        })
      },4000);
    this.setState({ 
      fileList,
    });
  } 


  beforeUpload = (file, fileList) => {

  }


  render(){
    const { getLoading, detailModalData, modifyModalData, fileList, previewVisible, previewImage, modalTitle } = this.state;
    const { Option } = Select; 
    const { getFieldDecorator } = this.props.form;  
    const uploadButton = (
      <div>
        <Icon type="plus" />
        <div className="ant-upload-text">添加</div>
      </div>
    );
    return(
      <PageHeaderWrapper>
        <div>
          <div  className={styles.example}>
            <Spin spinning={ getLoading } />
          </div>
          <Card>
            <RangePicker size="large" style={{margin: "0 0 15px 0"}} onChange={this.onChangeRangePicker} />
            <Divider type="vertical" />
            <Search 
              placeholder="请输入商品名称" 
              onSearch={this.onSearch} 
              enterButton="搜索"
              size="large"
              style={{ width: 320, height: "20px" }}
              />
              <Button size='large' style={{marginLeft: "15px"}} onClick={this.addData}>添加</Button>
            <Table 
            // pageSize={15}
                onRow={record => {
                return {
                    onClick: event => {

                    this.setState({
                        modalData: record,
                        RealName_billno: record.billno
                    });
                    }, 
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
              title={`${modalTitle}优惠商品`}
              visible={this.state.modalVisible}
              onOk={this.handleOk}
              onCancel={this.handleCancel}
            //   width={1000}
              maskClosable={false}
              footer={[
                <Button key="back" onClick={this.handleCancel}>
                  取消
                </Button>,
                // <Button type="danger" onClick={this.notAdopted}>
                //   未通过
                // </Button>,
                <Button key="submit" type="primary" onClick={this.handleSubmit}>
                  确定
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
                            onChange={this.handleChangePreview}
                          >
                            {fileList.length >= 6 ? null : uploadButton}
                          </Upload>
                          <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                            <img className={styles.previewImage} alt="example" style={{ width: '100%' }} src={previewImage} />
                          </Modal> 
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="商品名称：">
                            {getFieldDecorator('waresname', {
                            rules: [{ required: true, message: '请输入商品名称' }],
                            initialValue: modifyModalData.waresname,
                            })(
                            <Input placeholder="请输入商品名称" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="商品型号：">
                            {getFieldDecorator('model', {
                            rules: [{ required: true, message: '请输入商品型号' }],
                            initialValue: modifyModalData.model,
                            })(
                            <Input placeholder="请输入商品型号" />
                            )}
                        </Form.Item>
                      </Col>
                      <Col>
                        <Form.Item label="商品编码：">
                            {getFieldDecorator('productno', {
                            rules: [{ required: true, message: '请输入商品编码' }],
                            initialValue: modifyModalData.productno,
                            })(
                            <Input placeholder="请输入商品编码" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="产品编号：">
                            {getFieldDecorator('waresno', {
                            rules: [{ required: true, message: '请输入产品编号' }],
                            initialValue: modifyModalData.wareno,
                            })(
                            <Input placeholder="请输入产品编号" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="价格：">
                            {getFieldDecorator('price', {
                            rules: [{ required: true, message: '请输入价格' }],
                            initialValue: modifyModalData.price,
                            })(
                            <Input placeholder="请输入价格" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="单位：">
                            {getFieldDecorator('unit', {
                            rules: [{ required: true, message: '请输入单位' }],
                            initialValue: modifyModalData.unit,
                            })(
                            <Input placeholder="请输入单位" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="类型">
                            {getFieldDecorator('waretype', {
                            rules: [{ required: true, message: '请选择商城在售' }],
                            initialValue: modifyModalData.waretype,
                            })(
                            <Radio.Group>
                              <Radio value={0}>普通商品</Radio>
                              <Radio value={1}>优惠商品</Radio>
                            </Radio.Group>
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="数量">
                            {getFieldDecorator('qty', {
                            rules: [{ required: true, message: '请输入商品数量' }],
                            initialValue: modifyModalData.qty,
                            })(
                            <Input placeholder="请输入商品数量" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="次品">
                            {getFieldDecorator('qty1', {
                            rules: [{ required: true, message: '请输入商品次品数量' }],
                            initialValue: modifyModalData.qty1
                            })(
                            <Input placeholder="请输入商品次品数量" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                        <Form.Item label="坏品">
                            {getFieldDecorator('qty2', {
                            rules: [{ required: true, message: '请输入商品坏品数量' }],
                            initialValue: modifyModalData.qty2,
                            })(
                            <Input placeholder="请输入商品坏品数量" />
                            )}
                        </Form.Item>
                        </Col>
                        <Col>
                          <Form.Item label="其它：">
                              {getFieldDecorator('qty3', {
                              rules: [{ required: true, message: '请输入商品其它数量' }],
                              initialValue: modifyModalData.qty3,
                              })(
                              <Input placeholder="请输入商品其它数量" />
                              )}
                          </Form.Item>
                        </Col>
                        {/* <Col>
                          <Form.Item label="盘点日期：">
                              {getFieldDecorator('checkdate', {
                              rules: [{ required: true, message: '请选择盘点日期' }],
                              initialValue: selectDate!=""?selectDate:defaultEditData.checkdate
                              })(
                                <div>
                                    <Input hidden="hidden" value={selectDate!=""?selectDate:defaultEditData.checkdate} />
                                    <DatePicker
                                      style={{width:"100%",float:"left"}}
                                      onChange={this.oneDatePickerChange} 
                                      placeholder="请选择盘点日期"
                                      defaultValue={moment(defaultEditData.checkdate)}
                                    />
                                </div> 
                              )}
                          </Form.Item>
                        </Col> */}
                        <Col>
                          <Form.Item label="描述：">
                              {getFieldDecorator('description', {
                              rules: [{ required: true, message: '请输入描述信息' }],
                              initialValue: modifyModalData.description,
                              })(
                              <textarea style={{width:"100%",height:"100px"}} placeholder="请输入描述信息" />
                              )}
                          </Form.Item>
                        </Col>
                    </Row>
                </Form>
            </Modal>
          </div>
          {/* 详情 */}
          <Modal
              title="发票详情"
              visible={this.state.detailModalVisible}
              onCancel={this.detailHandleCancel}
            //   width={1000}
              maskClosable={false}
              footer={[
                <Button type="primary" key="back" onClick={this.detailHandleCancel}>
                  关闭
                </Button>,
              ]}
            >
              <div className={styles.detailContainer}>
                <p><span>商品图片：</span><span className="content"> <img style={{width:"180px",height:"180px"}} src={this.state.modalData.image1} alt="img" /></span></p>
                <p><span>商品名称：</span><span className="content">{this.state.modalData.waresname}</span></p>
                <p><span>商品billno：</span><span className="content">{this.state.modalData.billno}</span></p>
                <p><span>类型：</span><span className="content">{this.state.modalData.type==0?"进销的产品":"生产的产品"}</span></p>
                <p><span>商品：</span><span className="content">{this.state.modalData.series}</span></p>
                <p><span>型号：</span><span className="content">{this.state.modalData.model}</span></p>
                <p><span>价格：</span><span className="content">{this.state.modalData.price}</span></p>
                <p><span>商城在售：</span><span className="content">{this.state.modalData.onsale==0?"下架不在售":"上架在售"}</span></p>
                <p><span>单位：</span><span className="content">{this.state.modalData.unit}</span></p>
                <p><span>库存数量：</span><span className="content">{this.state.modalData.qty}</span></p>
                <p><span>次品：</span><span className="content">{this.state.modalData.qty1}</span></p>
                <p><span>坏品：</span><span className="content">{this.state.modalData.qty2}</span></p>
                <p><span>其他：</span><span className="content">{this.state.modalData.qty3}</span></p>
                <p><span>产品型号：</span><span className="content">{this.state.modalData.model}</span></p>
                <p><span>产品编码：</span><span className="content">{this.state.modalData.productno}</span></p>
                <p><span>操作员：</span><span className="content">{this.state.modalData.username}</span></p>
                <p><span>添加时间：</span><span className="content">{this.state.modalData.billdate}</span></p>
                <p><span>描述：</span><span className="content">{this.state.modalData.description}</span></p>
              </div>
            </Modal>
        </div>
      </PageHeaderWrapper>
    );
  }
}


export default PreferentialActivity;