export class LocalStorageManager {
    constructor(store){
        this.store=store;
    }

    save(){
        localStorage.setItem('quiz-data',JSON.stringify(this.store.getData()));
    }

    load(){
        const data=localStorage.getItem('quiz-data');
        if (data){
            this.store.data=JSON.parse(data);
        }
    }
}