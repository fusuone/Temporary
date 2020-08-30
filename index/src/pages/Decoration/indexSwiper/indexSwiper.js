import React, { PureComponent } from 'react';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import http from '@/utils/http';
import Api from '@/common/api';
import moment from 'moment';
import {
  Tabs,
  Form,
  Table,
  Card,
  Icon,
  Spin,
  Modal,
  message,
  Input,
  Row,
  Col,
  DatePicker,
  Upload,
} from 'antd';
import { connect } from 'dva';
const { TabPane } = Tabs;

function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}
@connect(({ user }) => ({
  currentUser: user.currentUser,
}))
@Form.create()
class indexSwiper extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false, //提交动画
      previewVisible: false, //预览可见
      previewImage: '', //base64地址
      fileList: [], //已上传的图片列表
      fileImage: [], //存放图片最终地址
      getLoading: false,
      indexkey:'',
      modalVisible: false, //是否显示表单
      modalData: {}, //编辑选中区域的值
      detailModalData: {}, //最终数据存放值
      begindate: '', //开始时间
      enddate: '', //结束时间
      reqParams: {
        admin: this.props.currentUser.admin, //管理员billno
        keyword: '',
        pagesize: 15,
        page: 1,
      },
      // 首页广告
      listData: {
        list: [],
        total: 0,
      },
      // 分类广告
      listData1: {
        list: [],
        total: 0,
      },
      //百货推荐
      listData2: {
        list: [],
        total: 0,
      },
      //酒水推荐
      listData3: {
        list: [],
        total: 0,
      },
      listData4: {
        list: [],
        total: 0,
      },
    };
    this.columns1 = [
      {
        title: '首页图片',
        width: 400,
        dataIndex: 'image',
        key: 'image',
        fixed: 'left',
        render: (text, record) => {
          return <div>
                 <img style={{ width: '150px', height: '100px' }} src={record.image} />
                 <img style={{ width: '150px', height: '100px',marginLeft:'20px' }} src={record.pic} />
                 </div>;
        },
      },
      {
        title: '推广部位',
        width: 150,
        dataIndex: 'descption',
        key: 'descption',
      },
      {
        title: '品牌名称',
        width: 150,
        dataIndex: 'keyword',
        key: 'keyword',
        location,
      },
      {
        title: '广告标题',
        width: 200,
        dataIndex: 'title',
        key: 'title',
      },
      {
        title: '开始时间',
        dataIndex: 'begindate',
        key: 'begindate',
        // width:200,
        render: (text, record) => {
          let txt = '';
          if (record.begindate == null) {
            text = '长期活动';
          } else if (record.begindate != null) {
            text = record.begindate;
          }
          return text;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a
                onClick={ev => {
                  this.UpdateModal(ev, record);
                }}
              >
                更改
              </a>
            </div>
          );
        },
      },
    ];
    this.columns2 = [
      {
        title: '首页图片',
        width: 400,
        dataIndex: 'image',
        key: 'image',
        fixed: 'left',
        render: (text, record) => {
          return <div>
                 <img style={{ width: '150px', height: '100px' }} src={record.image} />
                 <img style={{ width: '150px', height: '100px',marginLeft:'20px' }} src={record.pic} />
                 </div>;
        },
      },
      {
        title: '推广部位',
        width: 150,
        dataIndex: 'descption',
        key: 'descption',
      },
      {
        title: '品牌名称',
        width: 150,
        dataIndex: 'keyword',
        key: 'keyword',
        location,
      },
      {
        title: '广告标题',
        width: 200,
        dataIndex: 'title',
        key: 'title',
      },
      {
        title: '开始时间',
        dataIndex: 'begindate',
        key: 'begindate',
        // width:200,
        render: (text, record) => {
          let txt = '';
          if (record.begindate == null) {
            text = '长期活动';
          } else if (record.begindate != null) {
            text = record.begindate;
          }
          return text;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a
                onClick={ev => {
                  this.UpdateModal(ev, record);
                }}
              >
                更改
              </a>
            </div>
          );
        },
      },
    ];
    this.columns3 = [
      {
        title: '首页图片',
        width: 400,
        dataIndex: 'image',
        key: 'image',
        fixed: 'left',
        render: (text, record) => {
          return <div>
                 <img style={{ width: '150px', height: '100px' }} src={record.image} />
                 <img style={{ width: '150px', height: '100px',marginLeft:'20px' }} src={record.pic} />
                 </div>;
        },
      },
      {
        title: '推广部位',
        width: 150,
        dataIndex: 'descption',
        key: 'descption',
      },
      {
        title: '品牌名称',
        width: 150,
        dataIndex: 'keyword',
        key: 'keyword',
        location,
      },
      {
        title: '广告标题',
        width: 200,
        dataIndex: 'title',
        key: 'title',
      },
      {
        title: '开始时间',
        dataIndex: 'begindate',
        key: 'begindate',
        // width:200,
        render: (text, record) => {
          let txt = '';
          if (record.begindate == null) {
            text = '长期活动';
          } else if (record.begindate != null) {
            text = record.begindate;
          }
          return text;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a
                onClick={ev => {
                  this.UpdateModal(ev, record);
                }}
              >
                更改
              </a>
            </div>
          );
        },
      },
    ];
    this.columns4 = [
      {
        title: '首页图片',
        width: 400,
        dataIndex: 'image',
        key: 'image',
        fixed: 'left',
        render: (text, record) => {
          return <div>
                 <img style={{ width: '150px', height: '100px' }} src={record.image} />
                 <img style={{ width: '150px', height: '100px',marginLeft:'20px' }} src={record.pic} />
                 </div>;
        },
      },
      {
        title: '推广部位',
        width: 150,
        dataIndex: 'descption',
        key: 'descption',
      },
      {
        title: '品牌名称',
        width: 150,
        dataIndex: 'keyword',
        key: 'keyword',
        location,
      },
      {
        title: '广告标题',
        width: 200,
        dataIndex: 'title',
        key: 'title',
      },
      {
        title: '开始时间',
        dataIndex: 'begindate',
        key: 'begindate',
        // width:200,
        render: (text, record) => {
          let txt = '';
          if (record.begindate == null) {
            text = '长期活动';
          } else if (record.begindate != null) {
            text = record.begindate;
          }
          return text;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a
                onClick={ev => {
                  this.UpdateModal(ev, record);
                }}
              >
                更改
              </a>
            </div>
          );
        },
      },
    ];
    this.columns5 = [
      {
        title: '首页图片',
        width: 400,
        dataIndex: 'image',
        key: 'image',
        fixed: 'left',
        render: (text, record) => {
          return <div>
                 <img style={{ width: '150px', height: '100px' }} src={record.image} />
                 <img style={{ width: '150px', height: '100px',marginLeft:'20px' }} src={record.pic} />
                 </div>;
        },
      },
      {
        title: '推广部位',
        width: 150,
        dataIndex: 'descption',
        key: 'descption',
      },
      {
        title: '品牌名称',
        width: 150,
        dataIndex: 'keyword',
        key: 'keyword',
        location,
      },
      {
        title: '广告标题',
        width: 200,
        dataIndex: 'title',
        key: 'title',
      },
      {
        title: '开始时间',
        dataIndex: 'begindate',
        key: 'begindate',
        // width:200,
        render: (text, record) => {
          let txt = '';
          if (record.begindate == null) {
            text = '长期活动';
          } else if (record.begindate != null) {
            text = record.begindate;
          }
          return text;
        },
      },
      {
        title: '操作',
        key: 'operation',
        fixed: 'right',
        width: 130,
        render: (text, record) => {
          this.setState({
            RealName_billno: record.billno,
          });
          //操作菜单
          return (
            <div>
              <a
                onClick={ev => {
                  this.UpdateModal(ev, record);
                }}
              >
                更改
              </a>
            </div>
          );
        },
      },
    ];
  }
  ///渲染前
  componentDidMount() {
    this.getList();
  }
  //点击更改
  UpdateModal = (ev,record) => {
    this.state.fileImage[0]=record.image;
    this.state.fileList[0]={
      uid: 1,
      name: 'image',
      status: 'done',
      url:record.image,
    };
    this.state.fileList[1]={
      uid: 2,
      name: 'image',
      status: 'done',
      url:record.pic,
    };
    this.state.fileImage[1]=record.pic;
    this.props.form.setFieldsValue({
      keyword:record.keyword,
      title:record.title,
      begindates:(record.begindate!=null)?moment(record.begindate):moment("2000-01-01"),
      enddates:(record.enddate!=null)?moment(record.enddate):moment("2000-01-01")
    })
    this.setState({
      modalVisible:true,
      modalData:record,
      begindate:record.begindate,
      enddate:record.enddate
    })
  };
  //关闭预览
  handleCancelPreview = async file => {
    this.setState({ previewVisible: false });
  };
  //预览触发
  handlePreview = async file => {
    if (!file.url && !file.preview) {
      file.preview = await getBase64(file.originFileObj);
    }
    this.setState({
      previewImage: file.url || file.preview,
      previewVisible: true,
    });
  };
  //图片数量变化时触发
  handleChangePreview = ({ fileList }) => {
    const fileImageArray = [];
    for (let i = 0; i < fileList.length; i++) {
      let a = '';
      if (fileList[i].response) {
        a = fileList[i].response.data.source;
      } else {
        a = fileList[i].url;
      }
      fileImageArray[i] = a; //a 图片url地址
    }
    setTimeout(() => {
      this.setState({
        fileImage: fileImageArray,
      });
    }, 4000);
    this.setState({
      fileList,
    });
  };
  // 关闭并初始化
  handleCancel = async () => {
    this.props.form.resetFields();
    await this.setState({
      modalVisible: false,
      EditModalVisible: false,
      modalData: {},
      fileList: [],
      fileImage: [],
      previewImage: [],
      begindate: '',
      enddate: '',
    });
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
  };
  beforeUpload = (file, fileList) => {};
  //获取首页广告
  getList = () => {
    http({
      method: 'get',
      api: 'get_index_ad_0',
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            listData: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
  //获取分类广告
  getList1 = () => {
    http({
      method: 'get',
      api: 'get_index_ad_1',
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            listData1: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData1: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
  //获取百货推荐
  getList2 = () => {
    http({
      method: 'get',
      api: 'get_index_ad_2',
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            listData2: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData2: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
  //获取酒水推荐
  getList3 = () => {
    http({
      method: 'get',
      api: 'get_index_ad_3',
    })
      .then(result => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.setState({
            listData3: {
              list: data.list,
              total: Number(data.total),
            },
            getLoading: false,
          });
        } else {
          message.warn(msg);
          this.setState({
            listData3: [],
            getLoading: false,
          });
        }
      })
      .catch(() => {
        this.setState({ getLoading: false });
      });
  };
    //获取酒水推荐
    getList4 = () => {
      http({
        method: 'get',
        api: 'get_index_ad_4',
      })
        .then(result => {
          const { status, msg, data } = result;
          if (status === '0') {
            this.setState({
              listData4: {
                list: data.list,
                total: Number(data.total),
              },
              getLoading: false,
            });
          } else {
            message.warn(msg);
            this.setState({
              listData4: [],
              getLoading: false,
            });
          }
        })
        .catch(() => {
          this.setState({ getLoading: false });
        });
    };
  //切换时的回调
  callback = key => {
    switch (key) {
      case '1':
        this.setState({
          indexkey:key
        })
        this.getList();
        break;
      case '2':
          this.setState({
            indexkey:key
          })
        this.getList1();
        break;
      case '3':
          this.setState({
            indexkey:key
          })
        this.getList2();
        break;
      case '4':
          this.setState({
            indexkey:key
          })
        this.getList3();
        break;
      case '5':
          this.setState({
            indexkey:key
          })
        this.getList4();
        break;
    }
  };
  //取消
  detailHandleCancel =async (e) => {
    this.props.form.resetFields();
    this.props.form.setFields({"DatePicker":""})
    await this.setState({ 
      modalVisible: false,
      EditModalVisible: false,
      modalData: {},
      fileList: [],
      fileImage: [],
      previewImage:[],
      begindate:"",
      enddate:"",
    });
    this.handleCancel()
  };
  // 开始时间
  onChange_begindate=async(date, dateString)=>{
    await this.setState({
      begindate: dateString
    })
  }
  //结束时间
  onChange_enddates=async(date, dateStrings)=>{
    await this.setState({
      enddate: dateStrings
    })
  }
  //确定触发提交
  onoklHandleCancel =(e) => {
    // billno keyword image pic begindate enddate title 需要传的参数
    var billno,keyword,image,pic,begindate,enddate,title;
    this.setState({ loading: true });
    billno = this.state.modalData.billno
    keyword = document.getElementById('keyword').value;
    title = document.getElementById('title').value;
    begindate = this.state.begindate;
    enddate = this.state.enddate
    e.preventDefault();
    this.props.form.validateFields((err, value) => {
    if (!err) {
      if(this.state.fileList.length==0){
          message.info("请点击添加图片");
        return;
      }
      const data = {
        billno:this.state.modalData.billno,
        keyword:document.getElementById('keyword').value,
        title:document.getElementById('title').value,
        enddate:this.state.enddate,
        begindate: this.state.begindate,
        image:this.state.fileImage[0],
        pic:this.state.fileImage[1]
      }
      http({
        method: 'post',
        api: 'set_index_ad',
        data: {
          ...data
        }
      }).then((result) => {
        const { status, msg, data } = result;
        if (status === '0') {
          this.getList();
          this.getList1();
          this.getList2();
          this.getList3();
          this.getList4();
          message.info('成功');
          this.handleCancel();//点击关闭
        } else {
          message.info(msg);
        }this.setState( { 
          submitting: false,
          modalVisible: false, });
         }).catch(() => {
           this.setState({ submitting: false });
         });
    }
    this.setState({
      modalVisible: false,
    });
  });
  };
  render() {
    const {
      previewVisible,
      previewImage,
      fileList,
      getLoading,
      fileImage,
      listData,
      listData1,
      listData2,
      listData3,
      listData4,
    } = this.state;
    const uploadButton = (
      <div>
        <Icon type="plus" />
        <div className="ant-upload-text">添加</div>
      </div>
    );
    const { getFieldDecorator } = this.props.form;
    return (
      <PageHeaderWrapper>
        <div>
          <Spin spinning={getLoading} />
        </div>
        <Tabs defaultActiveKey="1" onChange={this.callback}>
          <TabPane tab="首页轮播图" key="1">
            <Card>
              <Table
                rowKey={record=>record.id}
                columns={this.columns1} //每行数据
                dataSource={listData.list} //数据来源
                scroll={{ x: 1500, y: 800 }} //高
                pagination={{
                  current: this.state.reqParams.page, //当前页数
                  onChange: this.handleTableChange, //页数改变的回调
                  pageSize: 15, //每页条数
                  defaultCurrent: 1, //默认的当前页数
                  //总页数
                  total: listData.total, //总条数
                }}
              />
            </Card>
          </TabPane>
          <TabPane tab="首页打折促销区域" key="2">
            <Card>
              <Table
                rowKey={record=>record.id}
                columns={this.columns2} //每行数据
                dataSource={listData1.list} //数据来源
                scroll={{ x: 1500, y: 800 }} //高
                pagination={{
                  current: this.state.reqParams.page, //当前页数
                  onChange: this.handleTableChange, //页数改变的回调
                  pageSize: 15, //每页条数
                  defaultCurrent: 1, //默认的当前页数
                  //总页数
                  total: listData1.total, //总条数
                }}
              />
            </Card>
          </TabPane>
          <TabPane tab="模式滚动广告专场" key="3">
            <Card>
              <Table
                rowKey={record=>record.id}
                columns={this.columns3} //每行数据
                dataSource={listData2.list} //数据来源
                scroll={{ x: 1500, y: 800 }} //高
                pagination={{
                  current: this.state.reqParams.page, //当前页数
                  onChange: this.handleTableChange, //页数改变的回调
                  pageSize: 15, //每页条数
                  defaultCurrent: 1, //默认的当前页数
                  //总页数
                  total: listData2.total, //总条数
                }}
              />
            </Card>
          </TabPane>
          <TabPane tab="百货商品" key="4">
            <Card>
              <Table
                rowKey={record=>record.id}
                columns={this.columns4} //每行数据
                dataSource={listData3.list} //数据来源
                scroll={{ x: 1500, y: 800 }} //高
                pagination={{
                  current: this.state.reqParams.page, //当前页数
                  onChange: this.handleTableChange, //页数改变的回调
                  pageSize: 15, //每页条数
                  defaultCurrent: 1, //默认的当前页数
                  //总页数
                  total: listData3.total, //总条数
                }}
              />
            </Card>
          </TabPane>
          <TabPane tab="酒水推荐" key="5">
            <Card>
              <Table
                rowKey={record=>record.id}
                columns={this.columns5} //每行数据
                dataSource={listData4.list} //数据来源
                scroll={{ x: 1500, y: 800 }} //高
                pagination={{
                  current: this.state.reqParams.page, //当前页数
                  onChange: this.handleTableChange, //页数改变的回调
                  pageSize: 15, //每页条数
                  defaultCurrent: 1, //默认的当前页数
                  //总页数
                  total: listData4.total, //总条数
                }}
              />
            </Card>
          </TabPane>
        </Tabs>
        <Modal
          title={`修改推广信息`}
          visible={this.state.modalVisible} //是否显示
          onCancel={this.detailHandleCancel} //取消触发
          onOk={this.onoklHandleCancel} //确定触发
          confirmLoading={this.state.submitting} //提交动画状态
          maskClosable={false} //是否强制渲染
        >
          <Form layout="vertical">
            <Row gutter={24}>
              <Col>
                <Form.Item label="请选择图片:1.首页展示 2.为详情">
                  <Upload
                    name="file"
                    action={Api['uploadimg']}
                    listType="picture-card"
                    fileList={fileList}
                    onPreview={this.handlePreview} //预览
                    onChange={this.handleChangePreview} //图片数量变化时触发
                  >
                    {fileList.length >= 2 ? null : uploadButton}
                  </Upload>
                  {/* 预览效果 */}
                  <Modal visible={previewVisible} footer={null} onCancel={this.handleCancelPreview}>
                    <img alt="example" style={{ width: '100%' }} src={previewImage} />
                  </Modal>
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="品牌名称：">
                  {getFieldDecorator('keyword', {
                    rules: [{ required: true, message: '请输入品牌' }],
                  })(<Input placeholder="请输入品牌" id="keyword" name="keyword" type="text" />)}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="广告标题：">
                  {getFieldDecorator('title', {
                    rules: [{ required: true, message: '请输入广告标题' }],
                  })(
                    <Input
                      placeholder="请输入您想显示的广告标题"
                      id="title"
                      name="title"
                      type="text"
                    />
                  )}
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="请选择开始日期">
                  {getFieldDecorator('begindate')(
                    <Input hidden="hidden"/>
                  )
                  }
                  {getFieldDecorator('begindates')(
                    <DatePicker
                    style={{ width: '100%' }}
                    onChange = {this.onChange_begindate}
                    showTime
                  />
                  )
                  }
                </Form.Item>
              </Col>
              <Col>
                <Form.Item label="请选择结束日期">
                  {getFieldDecorator('enddate')(
                    <Input hidden="hidden"/>
                  )
                  }
                  {getFieldDecorator('enddates')(
                    <DatePicker
                    style={{ width: '100%' }}
                    onChange = {this.onChange_enddates}
                    showTime
                  />
                  )
                  }
                </Form.Item>
              </Col>
            </Row>
          </Form>
        </Modal>
      </PageHeaderWrapper>
    );
  }
}
export default indexSwiper;
