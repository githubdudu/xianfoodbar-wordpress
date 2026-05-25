import 'antd/dist/antd.less';

import '../styles/app.css'

import React from 'react'
import ReactDOM from 'react-dom'
import { Route, BrowserRouter as Router, Switch } from 'react-router-dom'

import Admin from './admin'
import AdminLayouts from './components/admin-layouts';
import AdminForms from './forms'
import AdminNotFound from './notfound';
import OrderInfo from './orderInfo';
import AdminTable from './table'
import AdminTabsComponent from './tabs';

function App() {
    const root_url = (window as any).root_admin || '/admin';
    return (
        <Router>
            <AdminLayouts>
                <Switch>
                    <Route exact path={root_url} key="admin" component={Admin}>
                    </Route>
                    {/* <Route path="/admin" component={Admin}></Route> */}
                    <Route exact key="404"  path={root_url + "/404"} component={AdminNotFound}>
                    </Route>

                    <Route key="table"  path={ root_url +  "/system/table/:name/:anymethod/:anyparam"} component={AdminTable}>
                    </Route>

                    <Route key="table_argus" path={root_url +  "/system/table/:name/:anymethod"} component={AdminTable}>
                    </Route>

                    <Route key="forms"  path={root_url +  "/system/form/:name/:anymethod/:anyparam"} component={AdminForms}>
                    </Route>

                    <Route key="forms_argus"  path={root_url +  "/system/form/:name/:anymethod"} component={AdminForms}>
                    </Route>

                    <Route key="tabs" path={root_url +  "/system/tabs/:name/:anymethod/:anyparam"} component={AdminTabsComponent}>
                    </Route>

                    <Route key="tabs_argus" path={root_url +  "/system/tabs/:name/:anymethod"} component={AdminTabsComponent}>
                    </Route>

                    <Route key="orderinfo" path={root_url + "/order/view/:id"} component={OrderInfo}>
                    </Route>

                    <Route key="4042" path="*" component={AdminNotFound}>
                    </Route>

                </Switch>
            </AdminLayouts>
        </Router>
    );
}

ReactDOM.render(<App />, document.querySelector("#admin"));
