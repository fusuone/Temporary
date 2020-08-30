## 使用 normal

- 1. 
```
<ImagePick
  initialValue={[{
    status: 'done',
    uid: 'rc-upload-1540865311499-2',
    url: 'http://192.168.3.71:7071/team/images/20181030100835_3232.jpg'
  }, {
    status: 'done',
    uid: 'rc-upload-1540865311499-3',
    url: 'http://192.168.3.71:7071/team/images/20181030100852_4949.jpg'
  }]}
  onChange={fileList => console.log(fileList)}
/>
```

## 使用 @Form.create()

- 1. 设置 initialValue
```
<Form.Item label="图片">
  {getFieldDecorator('fileList', {
    initialValue: [{
      status: 'done',
      uid: 'rc-upload-1540865311499-2',
      url: 'http://192.168.3.71:7071/team/images/20181030100835_3232.jpg'
    }, {
      status: 'done',
      uid: 'rc-upload-1540865311499-3',
      url: 'http://192.168.3.71:7071/team/images/20181030100852_4949.jpg'
    }]
  })(
    <ImagePick />
  )}
</Form.Item>
```

- 2. 通过 setFieldsValue 设置
this.props.form.setFieldsValue({
  fileList: [{
    status: 'done',
    uid: 'rc-upload-1540865311499-9',
    url: 'http://192.168.3.71:7071/team/images/20181030105059_2224.jpg'
  }]
});