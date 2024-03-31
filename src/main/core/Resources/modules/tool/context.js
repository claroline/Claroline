import {createContext} from 'react'

const ToolContext = createContext({
  menu: [],
  actions: [],
  styles: []
})

export {
  ToolContext
}
