(function () {
    'use strict';

    function puantajTable() {
        var config = window.__ATTENDANCE_CONFIG__ || {};
        var statusList = config.attendanceStatuses || {};
        var nextStatus = config.nextStatus || {};
        var statusSymbols = {};
        Object.keys(statusList).forEach(function (k) {
            statusSymbols[k] = statusList[k].symbol || '';
        });

        return {
            month: config.month || '',
            search: '',
            personnel: config.personnelData || [],
            daysInMonth: config.daysInMonth || 31,
            loading: false,
            longLoading: false,
            loadingTimer: null,
            longLoadingTimer: null,
            fetchController: null,
            saving: false,
            savingKeys: new Set(),
            previousStatus: {},
            compactMode: localStorage.getItem('attendance-compact-mode') === 'true',
            selectedPersonnel: null,
            showQuickFill: false,
            showStats: false,
            quickFillPerson: '',
            quickFillStatus: 'full',
            quickFillStartDay: 1,
            quickFillEndDay: config.daysInMonth || 31,
            quickFillWeekdays: false,
            quickFillWeekends: false,
            quickFillMonSat: false,
            bulkFillInProgress: false,
            bulkFillPendingCount: 0,
            bulkFillTotalCount: 0,
            contextMenu: { show: false, x: 0, y: 0, personnelId: null, day: null },
            statusSymbols: statusSymbols,
            nextStatus: nextStatus,

            get totalStats() {
                var stats = {};
                Object.keys(statusList).forEach(function (k) { stats[k] = 0; });
                this.filteredPersonnel().forEach(function (p) {
                    Object.keys(stats).forEach(function (k) {
                        stats[k] += this.countStatus(p, k);
                    }.bind(this));
                }.bind(this));
                return stats;
            },

            getStatPercent: function (key) {
                var t = this.totalStats;
                var sum = Object.values(t).reduce(function (a, b) { return a + b; }, 0);
                return sum ? Math.round((t[key] / sum) * 100) : 0;
            },

            isToday: function (day) {
                var today = new Date();
                var checkDate = new Date(this.month + '-' + String(day).padStart(2, '0'));
                return today.toDateString() === checkDate.toDateString();
            },

            getStatusSymbol: function (status) { return this.statusSymbols[status] || ''; },
            getStatusIconClass: function (status) { return (status || 'none') + '-icon'; },
            getStatusTooltip: function (status, day, name) {
                var label = statusList[status] ? statusList[status].label : 'Bilinmeyen';
                return name + '\n' + day + ' - ' + label + '\nSol tık: Değiştir | Sağ tık: Menü';
            },

            filteredPersonnel: function () {
                if (!this.search) return this.personnel;
                var s = this.search.toLowerCase();
                return this.personnel.filter(function (p) {
                    return p.name && p.name.toLowerCase().indexOf(s) !== -1;
                });
            },

            getDayTotal: function (day) {
                return this.filteredPersonnel().filter(function (p) {
                    return p.attendance[day] && p.attendance[day] !== 'none';
                }).length;
            },

            showContextMenu: function (e, personnelId, day) {
                this.contextMenu = { show: true, x: e.pageX, y: e.pageY, personnelId: personnelId, day: day };
            },

            setStatusFromContext: function (status) {
                if (this.contextMenu.personnelId && this.contextMenu.day) {
                    this.setStatus(this.contextMenu.personnelId, this.contextMenu.day, status, null);
                }
                this.contextMenu.show = false;
            },

            cycleStatus: function (personnelId, day, event) {
                if (this.saving) return;
                var p = this.personnel.find(function (x) { return x.id === personnelId; });
                if (!p) return;
                var current = p.attendance[day] || 'none';
                var next = this.nextStatus[current] || 'full';
                this.setStatus(personnelId, day, next, event);
            },

            setStatus: function (personnelId, day, status, event) {
                var p = this.personnel.find(function (x) { return x.id === personnelId; });
                if (!p) return;
                var saveKey = personnelId + '-' + day;
                if (!this.previousStatus[saveKey]) this.previousStatus[saveKey] = p.attendance[day] || 'none';
                var btn = event && event.target && event.target.closest('.status-btn');
                if (btn) btn.classList.add('updating');
                p.attendance[day] = status;
                this.saveStatus(personnelId, day, status);
            },

            saveStatus: function (personnelId, day, status) {
                var self = this;
                var saveKey = personnelId + '-' + day;
                if (this.savingKeys && this.savingKeys.has(saveKey)) return;
                if (!this.savingKeys) this.savingKeys = new Set();
                this.savingKeys.add(saveKey);
                var date = this.month + '-' + String(day).padStart(2, '0');
                var storeUrl = config.storeUrl || '';
                var csrfToken = config.csrfToken || '';

                fetch(storeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ personnel_id: parseInt(personnelId, 10), date: date, status: status })
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                var msg = (data && data.message) ? data.message : ('Sunucu hatası (' + response.status + ')');
                                throw new Error(msg);
                            }
                            return data;
                        }, function () {
                            if (!response.ok) throw new Error('Sunucu hatası (' + response.status + ')');
                            return {};
                        });
                    })
                    .then(function (data) {
                        if (!data.success) throw new Error(data.message || 'Kaydetme hatası');
                        self.showSuccessAnimation(saveKey);
                    })
                    .catch(function (err) {
                        var p = self.personnel.find(function (x) { return x.id === personnelId; });
                        if (p && self.previousStatus[saveKey]) p.attendance[day] = self.previousStatus[saveKey];
                        self.showToast('Kaydetme hatası: ' + (err.message || 'Bilinmeyen'), 'error');
                    })
                    .finally(function () {
                        self.savingKeys.delete(saveKey);
                        if (self.bulkFillInProgress && self.bulkFillPendingCount > 0) {
                            self.bulkFillPendingCount--;
                            if (self.bulkFillPendingCount === 0) {
                                self.bulkFillInProgress = false;
                                self.showToast(self.bulkFillTotalCount + ' gün güncellendi.', 'success');
                            }
                        }
                    });
            },

            applyQuickFill: function () {
                var startDay = parseInt(this.quickFillStartDay, 10) || 1;
                var endDay = parseInt(this.quickFillEndDay, 10) || this.daysInMonth;
                if (startDay > endDay) {
                    this.showToast('Başlangıç günü bitişten büyük olamaz.', 'error');
                    return;
                }
                var personnelList = this.quickFillPerson
                    ? this.personnel.filter(function (p) { return p && String(p.id) === String(this.quickFillPerson); }.bind(this))
                    : this.personnel;
                if (this.quickFillPerson && (!personnelList || personnelList.length === 0)) {
                    this.showToast('Seçilen personel bulunamadı.', 'error');
                    return;
                }
                var count = 0;
                var useAllDays = this.quickFillWeekdays && this.quickFillWeekends;
                var useMonSat = this.quickFillMonSat && !useAllDays;
                for (var day = startDay; day <= endDay; day++) {
                    var d = new Date(this.month + '-' + String(day).padStart(2, '0'));
                    var dayOfWeek = d.getDay();
                    var isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                    var statusToApply = this.quickFillStatus;
                    if (useMonSat) {
                        if (dayOfWeek === 0) statusToApply = 'none';
                    } else if (!useAllDays) {
                        if (this.quickFillWeekdays && isWeekend) continue;
                        if (this.quickFillWeekends && !isWeekend) continue;
                    }
                    personnelList.forEach(function (p) {
                        if (p) {
                            p.attendance[day] = statusToApply;
                            this.saveStatus(p.id, day, statusToApply);
                            count++;
                        }
                    }.bind(this));
                }
                this.showQuickFill = false;
                if (count === 0) {
                    this.showToast('Aralıkta eşleşen gün yok.', 'success');
                    return;
                }
                this.bulkFillInProgress = true;
                this.bulkFillPendingCount = count;
                this.bulkFillTotalCount = count;
            },

            countStatus: function (p, status) {
                return Object.values(p.attendance).filter(function (s) { return s === status; }).length;
            },

            fetchTableAjax: function () {
                var self = this;
                if (this.fetchController) try { this.fetchController.abort(); } catch (e) {}
                this.fetchController = new AbortController();
                clearTimeout(this.loadingTimer);
                clearTimeout(this.longLoadingTimer);
                this.loadingTimer = setTimeout(function () { self.loading = true; }, 250);
                this.longLoadingTimer = setTimeout(function () { self.longLoading = true; }, 1200);
                var apiTableUrl = config.apiTableUrl || '';

                fetch(apiTableUrl + '?month=' + encodeURIComponent(this.month), {
                    headers: { 'Accept': 'application/json' },
                    signal: this.fetchController.signal
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data && data.success) {
                            self.personnel = data.personnelData || [];
                            self.daysInMonth = data.daysInMonth;
                        }
                    })
                    .catch(function () { self.showToast('Ağ hatası.', 'error'); })
                    .finally(function () {
                        clearTimeout(self.loadingTimer);
                        clearTimeout(self.longLoadingTimer);
                        self.loading = false;
                        self.longLoading = false;
                        self.fetchController = null;
                    });
            },

            showToast: function (msg, type) {
                var toast = document.createElement('div');
                toast.className = 'toast-notification toast-' + type;
                var iconChar = type === 'success' ? '\u2713' : '\u26A0';
                toast.innerHTML = '<div class="toast-icon-wrap">' + iconChar + '</div><span class="toast-message">' + msg + '</span>';
                document.body.appendChild(toast);
                setTimeout(function () { toast.classList.add('show'); }, 10);
                setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () { toast.remove(); }, 350);
                }, 2800);
            },

            showSuccessAnimation: function (saveKey) {
                if (!this.bulkFillInProgress) this.showToast('Kaydedildi.', 'success');
                if (this.previousStatus[saveKey]) delete this.previousStatus[saveKey];
                var self = this;
                setTimeout(function () {
                    document.querySelectorAll('.status-btn.updating').forEach(function (btn) { btn.classList.remove('updating'); });
                }, 1000);
            },

            exportExcel: function () {
                var headers = ['Personel'];
                for (var d = 1; d <= this.daysInMonth; d++) headers.push(d.toString());
                Object.keys(statusList).forEach(function (k) { headers.push(statusList[k].label); });
                var csv = '\ufeff' + headers.join(';') + '\n';
                this.filteredPersonnel().forEach(function (p) {
                    var row = [p.name];
                    for (var d = 1; d <= this.daysInMonth; d++) row.push(this.getStatusSymbol(p.attendance[d] || 'none'));
                    Object.keys(statusList).forEach(function (k) { row.push(this.countStatus(p, k)); }.bind(this));
                    csv += row.join(';') + '\n';
                }.bind(this));
                var link = document.createElement('a');
                link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
                link.download = 'puantaj_' + this.month + '.csv';
                link.click();
                this.showToast('Excel indirildi.', 'success');
            },

            toggleQuickFill: function () {
                this.showQuickFill = !this.showQuickFill;
            },

            toggleStats: function () {
                this.showStats = !this.showStats;
            },

            printTable: function () {
                var w = window.open('', '_blank');
                var tableEl = document.querySelector('.table-container');
                var tableHtml = tableEl ? tableEl.innerHTML.replace(/<\/script/gi, '<\\/script') : '';
                var title = 'Puantaj - ' + this.month;
                var endTitle = '<' + '/title>';
                var endStyle = '<' + '/style>';
                var endHead = '<' + '/head>';
                var endH2 = '<' + '/h2>';
                var endBody = '<' + '/body><' + '/html>';
                var doc = w.document;
                doc.write('<html><head><title>' + title + endTitle + '<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;} th,td{border:1px solid #ddd;padding:6px;}' + endStyle + endHead + '<body><h2>' + title + endH2 + tableHtml + endBody);
                doc.close();
                w.print();
                w.close();
            },

            toggleFullscreen: function () {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(function () {});
                else document.exitFullscreen();
            },

            init: function () {
                var self = this;
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        self.showQuickFill = false;
                        self.showStats = false;
                        self.contextMenu.show = false;
                    }
                });
            }
        };
    }

    window.puantajTable = puantajTable;
})();
