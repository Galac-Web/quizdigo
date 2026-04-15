export class QuizStorage {
  constructor(store, storageKey = 'quiz_builder_data') {
    this.store = store;
    this.storageKey = storageKey;
  }

  save() {
    try {
      const data = this.store.getData();
      localStorage.setItem(this.storageKey, JSON.stringify(data));
      return true;
    } catch (error) {
      console.error('Save localStorage failed:', error);
      return false;
    }
  }

  load() {
    try {
      const raw = localStorage.getItem(this.storageKey);
      if (!raw) return false;

      const parsed = JSON.parse(raw);
      if (!parsed || !Array.isArray(parsed.slides)) return false;

      this.store.setData(parsed);
      return true;
    } catch (error) {
      console.error('Load localStorage failed:', error);
      return false;
    }
  }

  clear() {
    try {
      localStorage.removeItem(this.storageKey);
      return true;
    } catch (error) {
      console.error('Clear localStorage failed:', error);
      return false;
    }
  }
}