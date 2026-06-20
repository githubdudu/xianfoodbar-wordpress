import React from 'react';
import { Route, BrowserRouter, Switch } from 'react-router-dom'
import Admin from './admin'
import AdminNotFound from './notfound';
import AdminTable from './table'
import AdminForms from './forms'
import AdminLayouts from './components/admin-layouts';
import AdminTabsComponent from './tabs';
import OrderInfo2 from './orderInfo';

window.addEventListener("beforeunload", function (e) {
  // Do something
  alert(1111111);
});
export default function CreateRouter(props: any) {
  const root_url = (window as any).root_admin || '/admin';
  return (
    <BrowserRouter>
      <AdminLayouts>
        <Switch>
          <Route exact path={root_url} component={Admin}>
          </Route>
          {/* <Route path="/admin" component={Admin}></Route> */}
          <Route exact path={root_url + "/404"} component={AdminNotFound}>
          </Route>

          <Route path={root_url + "/system/table/:name/:anymethod"} component={AdminTable}>
          </Route>

          <Route path={root_url + "/system/table/:name/:anymethod/:anyparam"} component={AdminTable}>
          </Route>

          <Route path={root_url + "/system/form/:name/:anymethod"} component={AdminForms}>
          </Route>

          <Route path={root_url + "/system/form/:name/:anymethod/:anyparam"} component={AdminForms}>
          </Route>

          <Route path={root_url + "/system/tabs/:name/:anymethod"} component={AdminTabsComponent}>
          </Route>

          <Route path={root_url + "/system/tabs/:name/:anymethod/:anyparam"} component={AdminTabsComponent}>
          </Route>

          <Route key="orderinfo" path={root_url + "/order/view/:id"} component={OrderInfo2}>
          </Route>

          <Route path="*">
            {props.children}
          </Route>
        </Switch>
      </AdminLayouts>
    </BrowserRouter>
  );
}
