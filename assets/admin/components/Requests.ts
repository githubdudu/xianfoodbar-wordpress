import { notification } from "antd";

export default class Requests {
  method: string = 'GET';
  url: string = '';
  id: string = '';
  includeData: object = {};
  idData: any;
  ref: any;

  setMethod(method: string = 'GET') {
    this.method = method;
    return this;
  }

  setUrl(url: string) {
    this.url = url;
    return this;
  }

  setIdName(id: string) {
    this.id = id;
    return this;
  }

  setIncludeData(includeData: object = {}) {
    this.includeData = includeData;
    return this;
  }

  setIdData(data: any) {
    // @ts-ignore
    this.idData = data;
    return this;
  }

  setRef(ref: any) {
    this.ref = ref;
    return this;
  }

  run() {
    let option = {};

    if (this.method != 'GET') {
      // @ts-ignore
      option['body'] = JSON.stringify(this.includeData);
    }

    fetch(`${this.url}/${this.idData}`, {
      ...option,
      method: this.method
    }).then(res => res.json()).then((data: any) => {
      if (data) {
        notification?.success({
          message: data.title,
          description: data.message,
        });
        (window as any).oldRequest = '';
        if (this.ref) {
          //@ts-ignore
          this.ref.current.reloadAndRest();
        }
      }
    }).catch((e: any) => {
      notification.error({
        message: '请求错误: ' + e.name,
        description: e.message,
      })
    })
  }
}
