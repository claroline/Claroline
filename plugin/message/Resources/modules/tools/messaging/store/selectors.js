
const message = (state) => state.currentMessage
const reply = (state) => state.messageForm.reply
const mailNotified = (state) => state.mailNotified

export const selectors = {
  message,
  reply,
  mailNotified
}
