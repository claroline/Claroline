import React from 'react'
import {trans} from '#/main/app/intl'

const ColorChart = (props) =>
  <div
    style={{
      width: "100%",
      maxWidth: "400px",
      paddingBottom: "20px"
    }}>
    <h3
      className="h4">
      {trans(props.name)}
    </h3>
    <div
      style={{
        display: "flex",
        width: "100%"
      }}>
      {props.colors.map((color) =>
        <div
          style={{
            flex: 1,
            backgroundColor: color,
            height: "30px"
          }}></div>)}
    </div>
  </div>

const AppearanceColorCharts = (props) => {
  return (
    <div></div>);
};

export {
  ColorChart, AppearanceColorCharts
}
