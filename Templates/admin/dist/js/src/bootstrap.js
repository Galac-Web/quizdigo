import { QuizApp } from './QuizApp.js';

document.addEventListener('DOMContentLoaded', async () => {
  const app = new QuizApp(document);
  window.quizApp = app;
  await app.init();
});