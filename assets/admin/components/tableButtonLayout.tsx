import { Button, notification } from 'antd';

import { Link } from 'react-router-dom';
import React from 'react';
import { useRequest } from 'ahooks';

export default function TableLayoutButton(props: {
  ajax?: string,
  className?: string,
  hidden?: string,
  color?: string,
  icon?: any,
  isRouter?: boolean,
  link?: string,
  ajaxId?: string,
  ajaxType?: string,
  ajaxIncludeData?: any,
  children?: any,
  onSuccess?: any,
}) {

  const { ajax, className, hidden, color, icon, isRouter, ajaxId, ajaxIncludeData, ajaxType, children, link } = props;
  const emptyRequest = useRequest((api_link: string = '', id: number | string = 0, method: string = "GET", body: any = {}) => ({
    url: `${api_link}/${id}`,
    method: method,
    body: JSON.stringify(body)
  }), {
    manual: true,
    onSuccess: (data: any) => (notification.success({
      message: data.title,
      description: data.message,
    }), props.onSuccess && props.onSuccess()),
    onError: (e) => notification.error({
      message: '请求错误: ' + e.name,
      description: e.message,
    })
  });

  const iconElement = React.createElement(icon || 'span')

  return (
    <React.Fragment>
      {isRouter && <Link to={link + "/" + ajaxId + "?id=" + ajaxId}>
        <Button
          type="primary"
          style={color ? { background: color, borderColor: color } : {}}
          icon={iconElement || <span></span>}
          className={className}
          hidden={!!hidden || false}>{children}</Button>
      </Link>}

      {isRouter === false && !!ajax === false && <Button
        type="primary"
        style={color ? { background: color, borderColor: color } : {}}
        icon={iconElement || <span></span>}
        className={className}
        href={link + "/" + ajaxId + "?id=" + ajaxId}
        hidden={!!hidden || false}>{children}</Button>}

      {!!ajax && isRouter === false && <Button
        type="primary"
        style={color ? { background: color, borderColor: color } : {}}
        icon={iconElement || <span></span>}
        className={className}
        onClick={() => emptyRequest.run(ajax, ajaxId, ajaxType || 'GET', ajaxIncludeData || {})}
        hidden={!!hidden || false}>{children}</Button>}
    </React.Fragment>
  )
}
