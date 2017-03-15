import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'

const Item = props => {
  return(
    <button type="button"
      onClick={props.onClick}
      className={classes(
        'answer-item choice-item',
        {'selected-answer': props.selected}
      )}>
        {props.choice.data}
    </button>
  )
}

Item.propTypes = {
  choice: T.shape({
    id: T.string.isRequired,
    data: T.string.isRequired
  }).isRequired,
  selected: T.bool.isRequired,
  onClick: T.func.isRequired
}

class BooleanPlayer extends Component {
  constructor(props){
    super(props)
    this.state = {
      selected: undefined
    }
  }

  handleItemClick(choice) {
    this.setState({selected: choice.id})
    this.props.onChange(
      choice.id
    )
  }

  render(){
    return (
      <div className="boolean-player row">
        {this.props.item.choices.map(choice =>
          <div key={choice.id}  className="col-md-6 col-sm-6">
            <Item
              selected={this.state.selected && this.state.selected === choice.id ? true : false}
              onClick={() => this.handleItemClick(choice)} choice={choice}/>
          </div>
        )}
      </div>
    )
  }
}

BooleanPlayer.propTypes = {
  item: T.shape({
    choices: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired
  }).isRequired,
  answer: T.string.isRequired,
  onChange: T.func.isRequired
}


BooleanPlayer.defaultProps = {
  answer: ''
}

export {BooleanPlayer}
