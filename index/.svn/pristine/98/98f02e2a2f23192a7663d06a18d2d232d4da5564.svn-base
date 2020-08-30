import React from 'react';
import { Upload, Button, Icon, Input } from 'antd';

const fileList = [{
    uid: '-1',
    name: 'xxx.png',
    status: 'done',
    url: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
    thumbUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
  }];
  
  const props = {
    action: '//jsonplaceholder.typicode.com/posts/',
    listType: 'picture',
    defaultFileList: [...fileList],
  };
  
class Release extends React.PureComponent{
    render(){
        return(
           <div>
                <li>发布商品</li>
                <br />
                <Input placeholder="商品名称" />
                <br />
                <br />
                <Upload {...props}>
                    <Button>
                    <Icon type="upload" /> 添加商品图片
                    </Button>
                </Upload>
                <br />
           </div>
        );
    }

}

export default Release;             