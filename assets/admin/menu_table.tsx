import '../bootstrap'
import './styles/show.sass'

import React, { useEffect, useState } from 'react'

import ReactDOM from 'react-dom'
import { Table } from 'antd';
import { useSessionStorageState } from 'ahooks'

window.onunload = (e) => {
  e.preventDefault();
  (window as any).menuEvent?.close();
}

function MenuTable() {

  const [TabelData, setTabelData] = useState<{
    data: any,
    column: any,
  }>({
    data: [],
    column: [],
  })

  const [CheckOrder, setCheckOrder] = useState("");
  // const [ordata, setOrdata] = useSessionStorageState('ordata', '');

  useEffect(() => {
    // window.sessionStorage.removeItem('ordata');
    window.sessionStorage.clear();
    const requestSource = () => {
      const event = new EventSource('/api/admin/menu_info');
      (window as any).menuEvent = event;
      event.onmessage = (data: any) => {
        if (data.data) {
          // setOrdata(data.data);
          data = JSON.parse(data.data);
          // console.log(data);
          data.column.forEach((item: any, index: number) => {
            data.column[index]['render'] = (text: string, recore: any) => <>{text && <span data-select={recore.selected_menu_id} className={'col-' + recore.menu_id} data-id={recore.menu_id || 0}>{text}</span>}</>
          })

          setTabelData({
            column: data.column,
            data: data.data,
          });
        }
      }

      event.onerror = (e) => {
        // eventListen.close();
        console.log(e)
        event.close();
        requestSource();
      }

      window.onbeforeunload = () => {
        event.close();
      }

      setTimeout(() => location.reload(), 1800000);
    }
    requestSource();
  }, [])


  useEffect(() => {
    // @ts-ignore
    document.querySelectorAll('td').forEach((item, index) => {
      // @ts-ignore
      if (item.classList.contains('add_new_menu') === false) {
        // @ts-ignore
        item.classList.remove('is_new_added');
      }
    })

    // @ts-ignore
    document.querySelectorAll('tr').forEach((item, index) => {
      // @ts-ignore
      const elemetChildName = item.querySelector('td.is_menu')
      //.textContent;

      if (elemetChildName) {
        // @ts-ignore
        const base = elemetChildName.querySelector('span');
        // @ts-ignore
        item.querySelectorAll('td.add_new_menu span.col-' + base.dataset.id).forEach((item2: Element, index2) => {
          // @ts-ignore
          if (item2.parentElement.querySelector('span[data-select="' + base.dataset.id + '"]')) {
            // @ts-ignore
            item2.parentElement.classList.add('is_new_added');
          }
        })
      }
    });
  }, [TabelData])
  // useRequest(, {
  //     debounceInterval: 500,
  //     pollingInterval: 1000,
  //     onSuccess(data: any) {
  //         // @ts-ignore

  //     }
  // });

  return (
    <div className="admin_show">
      <Table
        size="small"
        bordered
        pagination={false}
        scroll={{ x: 1200 }}
        dataSource={TabelData.data || []}
        columns={TabelData.column || []}
      ></Table>
    </div>
  );
}


ReactDOM.render(<MenuTable />, document.getElementById('admin'))
