import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import { AppStoreProvider } from './context/AppStore';
import './styles/global.css';
import './styles/layouts.css';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <AppStoreProvider>
        <App />
      </AppStoreProvider>
    </BrowserRouter>
  </StrictMode>,
);
