/**
 * Listening Analytics
 * Rastreia comportamento de audição do usuário
 * v1.1 - encoding corrigido
 */

class ListeningAnalytics {
    constructor() {
        this.STORAGE_KEY = 'memorias_listening_data';
        this.SESSION_KEY = 'memorias_current_session';
        this.userId = this.getUserId();
        this.sessionId = this.getSessionId();
        this.currentTrack = null;
        this.trackStartTime = null;

        this.init();
    }

    /**
     * Gera ou recupera ID único do usuário
     */
    getUserId() {
        let userId = localStorage.getItem('memorias_user_id');
        if (!userId) {
            userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('memorias_user_id', userId);
        }
        return userId;
    }

    /**
     * Gera ID da sessão atual
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem(this.SESSION_KEY);
        if (!sessionId) {
            sessionId = 'sess_' + Date.now();
            sessionStorage.setItem(this.SESSION_KEY, sessionId);
        }
        return sessionId;
    }

    /**
     * Inicializa analytics
     */
    init() {
        const data = this.getData();

        // Nova sessão
        data.visits = (data.visits || 0) + 1;
        data.lastVisit = new Date().toISOString();

        this.saveData(data);

        console.log('📊 Analytics inicializado:', {
            userId: this.userId,
            sessionId: this.sessionId,
            visits: data.visits
        });
    }

    /**
     * Carrega dados do localStorage
     */
    getData() {
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            return stored ? JSON.parse(stored) : this.getDefaultData();
        } catch (e) {
            console.error('Erro ao carregar analytics:', e);
            return this.getDefaultData();
        }
    }

    /**
     * Dados padrão
     */
    getDefaultData() {
        return {
            userId: this.userId,
            sessions: [],
            totalPlayTime: 0,
            albumCompleted: false,
            albumCompletedDate: null,
            tracksCompleted: [],
            tracksListened: {},
            totalTracks: 10,
            visits: 0,
            lastVisit: null,
            firstVisit: new Date().toISOString(),
            hasSeenSupportModal: false,
            hasSeenSupportModalPartial: false,
            hasSeenSupportModalReturning: false
        };
    }

    /**
     * Salva dados
     */
    saveData(data) {
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            console.error('Erro ao salvar analytics:', e);
        }
    }

    /**
     * Registra início de música
     */
    trackStart(trackId, trackName) {
        this.currentTrack = {
            id: trackId,
            name: trackName,
            startTime: Date.now(),
            playedSeconds: 0
        };

        console.log('▶️ Música iniciada:', trackName);
    }

    /**
     * Atualiza progresso da música
     */
    trackProgress(currentTime, duration) {
        if (!this.currentTrack) return;

        this.currentTrack.currentTime = currentTime;
        this.currentTrack.duration = duration;
        this.currentTrack.playedSeconds = currentTime;
        this.currentTrack.completionPercent = (currentTime / duration) * 100;
    }

    /**
     * Registra conclusão de música (80%+)
     */
    trackComplete(trackId, trackName) {
        const data = this.getData();

        // Adiciona às completadas (sem duplicatas)
        if (!data.tracksCompleted.includes(trackId)) {
            data.tracksCompleted.push(trackId);
        }

        // Incrementa contador de audições
        if (!data.tracksListened[trackId]) {
            data.tracksListened[trackId] = 0;
        }
        data.tracksListened[trackId]++;

        // Verifica se completou o álbum
        if (data.tracksCompleted.length === data.totalTracks && !data.albumCompleted) {
            data.albumCompleted = true;
            data.albumCompletedDate = new Date().toISOString();
            console.log('🎉 ÁLBUM COMPLETO!');
            this.saveData(data);
            this.onAlbumComplete();
            return;
        }

        this.saveData(data);

        console.log('✅ Música completa:', trackName, {
            completedTracks: data.tracksCompleted.length,
            total: data.totalTracks
        });
    }

    /**
     * Callback quando completa álbum
     */
    onAlbumComplete() {
        window.dispatchEvent(new CustomEvent('albumCompleted', {
            detail: this.getListeningStats()
        }));
    }

    /**
     * Estatísticas de audição
     */
    getListeningStats() {
        const data = this.getData();

        let favoriteTrack = null;
        let maxPlays = 0;
        for (const [trackId, plays] of Object.entries(data.tracksListened)) {
            if (plays > maxPlays) {
                maxPlays = plays;
                favoriteTrack = parseInt(trackId);
            }
        }

        return {
            userId: this.userId,
            visits: data.visits,
            tracksCompleted: data.tracksCompleted.length,
            totalTracks: data.totalTracks,
            completionRate: (data.tracksCompleted.length / data.totalTracks) * 100,
            albumCompleted: data.albumCompleted,
            albumCompletedDate: data.albumCompletedDate,
            favoriteTrack: favoriteTrack,
            tracksListened: data.tracksListened,
            firstVisit: data.firstVisit,
            lastVisit: data.lastVisit,
            isReturningUser: data.visits > 1
        };
    }

    /**
     * Verifica se deve mostrar modal de apoio
     * Retorna o motivo ou null se não deve mostrar
     */
    shouldShowSupportModal() {
        const data = this.getData();

        // 1. Completou o álbum (prioridade máxima)
        if (data.albumCompleted && !data.hasSeenSupportModal) {
            return 'album_complete';
        }

        // 2. Ouviu 3+ músicas (usuário engajado)
        if (data.tracksCompleted.length >= 3 && !data.hasSeenSupportModalPartial) {
            return 'partial_engagement';
        }

        // 3. Voltou 2+ vezes com 2+ músicas (usuário recorrente)
        if (data.visits >= 2 && data.tracksCompleted.length >= 2 && !data.hasSeenSupportModalReturning) {
            return 'returning_user';
        }

        return null;
    }

    /**
     * Marca que viu o modal para não repetir
     */
    markSupportModalSeen(reason) {
        const data = this.getData();

        if (reason === 'album_complete') {
            data.hasSeenSupportModal = true;
        } else if (reason === 'partial_engagement') {
            data.hasSeenSupportModalPartial = true;
        } else if (reason === 'returning_user') {
            data.hasSeenSupportModalReturning = true;
        }

        this.saveData(data);
        console.log('📊 Modal marcado como visto:', reason);
    }
}

// Inicializa globalmente
window.listeningAnalytics = new ListeningAnalytics();

console.log('📊 Listening Analytics carregado!');
