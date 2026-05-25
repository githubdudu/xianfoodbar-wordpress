import { Link } from 'react-router-dom';
import React from 'react';

export function Links(props: {
    children: any,
    link: string,
    isRouter: boolean,
    isBlank?: boolean
}) {

    return <React.Fragment>
        {props.isRouter ?
            <Link to={props.link}>
                {props.children}
            </Link> :
            <a href={props.link} target={props.isBlank ? "_blank" : "_self"}>
                {props.children}
            </a>}
    </React.Fragment>
}