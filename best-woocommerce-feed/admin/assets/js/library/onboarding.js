"use strict";
var LinnoOnboarding = (() => {
  var __defProp = Object.defineProperty;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
  var __export = (target, all) => {
    for (var name in all)
      __defProp(target, name, { get: all[name], enumerable: true });
  };
  var __copyProps = (to, from, except, desc) => {
    if (from && typeof from === "object" || typeof from === "function") {
      for (let key of __getOwnPropNames(from))
        if (!__hasOwnProp.call(to, key) && key !== except)
          __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
    }
    return to;
  };
  var __toCommonJS = (mod) => __copyProps(__defProp({}, "__esModule", { value: true }), mod);
  var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);

  // src/index.ts
  var src_exports = {};
  __export(src_exports, {
    Lifecycle: () => Lifecycle,
    LinnoOnboardingEngine: () => LinnoOnboardingEngine,
    LocalStorageAdapter: () => LocalStorageAdapter,
    Tracker: () => Tracker,
    VanillaAdapter: () => VanillaAdapter,
    engine: () => engine,
    registerOnboarding: () => registerOnboarding,
    tracker: () => tracker
  });

  // src/storage/interface.ts
  var LocalStorageAdapter = class {
    async get(key) {
      const item = localStorage.getItem(key);
      return item ? JSON.parse(item) : null;
    }
    async set(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    }
    async remove(key) {
      localStorage.removeItem(key);
    }
  };

  // src/core/lifecycle.ts
  var Lifecycle = class {
    constructor() {
      __publicField(this, "status", "registered");
    }
    getStatus() {
      return this.status;
    }
    transitionTo(newStatus) {
      this.status = newStatus;
    }
  };

  // src/core/tracker.ts
  var Tracker = class {
    constructor() {
      __publicField(this, "listeners", {});
    }
    on(event, handler) {
      if (!this.listeners[event]) {
        this.listeners[event] = [];
      }
      this.listeners[event].push(handler);
    }
    off(event, handler) {
      if (!this.listeners[event]) return;
      this.listeners[event] = this.listeners[event].filter((h) => h !== handler);
    }
    emit(event, payload) {
      if (this.listeners[event]) {
        this.listeners[event].forEach((handler) => handler(payload));
      }
    }
  };
  var tracker = new Tracker();

  // src/core/validator.ts
  var Validator = class {
    static validate(config) {
      if (!config.plugin) {
        throw new Error("[LinnoOnboarding] Plugin name is required.");
      }
      if (!config.steps || config.steps.length === 0) {
        throw new Error("[LinnoOnboarding] At least one step is required.");
      }
      if (!config.firstStrike) {
        throw new Error("[LinnoOnboarding] First Strike configuration is mandatory.");
      }
      if (typeof config.firstStrike.verify !== "function") {
        throw new Error("[LinnoOnboarding] First Strike must provide a verify function.");
      }
    }
  };

  // src/core/engine.ts
  var LinnoOnboardingEngine = class {
    constructor(storage) {
      __publicField(this, "config");
      __publicField(this, "storage");
      __publicField(this, "lifecycle");
      __publicField(this, "persistState", false);
      __publicField(this, "currentStepIndex", 0);
      __publicField(this, "completedSteps", /* @__PURE__ */ new Set());
      this.storage = storage || new LocalStorageAdapter();
      this.lifecycle = new Lifecycle();
    }
    async register(config) {
      Validator.validate(config);
      this.config = config;
      this.persistState = config.persistState ?? false;
      if (this.persistState) {
        await this.restoreState();
      }
      this.lifecycle.transitionTo("registered");
      tracker.emit("onboarding_registered", { plugin: config.plugin });
    }
    async start() {
      if (!this.config) {
        console.error("LinnoOnboarding: Cannot start before registration.");
        return;
      }
      if (this.lifecycle.getStatus() === "registered") {
        this.lifecycle.transitionTo("started");
        tracker.emit("onboarding_started", { plugin: this.config.plugin });
        if (this.config.telemetry?.onSetupStarted) {
          await Promise.resolve(this.config.telemetry.onSetupStarted({
            plugin: this.config.plugin,
            version: this.config.version
          }));
        }
        if (this.persistState) {
          await this.saveState();
        }
      }
    }
    getCurrentStep() {
      if (!this.config || !this.config.steps) return null;
      if (this.currentStepIndex < this.config.steps.length) {
        return this.config.steps[this.currentStepIndex];
      }
      return null;
    }
    async completeStep(stepId) {
      const step = this.config.steps.find((s) => s.id === stepId);
      if (!step) return;
      this.completedSteps.add(stepId);
      tracker.emit("step_completed", { stepId, plugin: this.config.plugin });
      if (this.currentStepIndex < this.config.steps.length - 1) {
        this.currentStepIndex++;
        this.lifecycle.transitionTo("step_in_progress");
        tracker.emit("step_changed", { index: this.currentStepIndex });
      } else {
        if (this.completedSteps.size >= this.config.steps.length) {
          this.lifecycle.transitionTo("onboarding_completed");
          tracker.emit("onboarding_completed", { plugin: this.config.plugin });
          if (this.config.telemetry?.onSetupCompleted) {
            await Promise.resolve(this.config.telemetry.onSetupCompleted({
              plugin: this.config.plugin,
              version: this.config.version
            }));
          }
          await this.verifyFirstStrike();
        }
      }
      if (this.persistState) {
        await this.saveState();
      }
    }
    async verifyFirstStrike() {
      const passed = await this.config.firstStrike.verify();
      if (passed) {
        this.lifecycle.transitionTo("first_strike_verified");
        tracker.emit("first_strike_verified", { plugin: this.config.plugin });
        if (this.config.telemetry?.onFirstStrikeCompleted) {
          await Promise.resolve(this.config.telemetry.onFirstStrikeCompleted({
            plugin: this.config.plugin,
            version: this.config.version
          }));
        }
        if (this.persistState) {
          await this.saveState();
        }
        return true;
      }
      return false;
    }
    async saveState() {
      if (!this.persistState) return;
      await this.storage.set(`linno_onboarding_${this.config.plugin}`, {
        status: this.lifecycle.getStatus(),
        currentStepIndex: this.currentStepIndex,
        completedSteps: Array.from(this.completedSteps)
      });
    }
    async restoreState() {
      const data = await this.storage.get(`linno_onboarding_${this.config.plugin}`);
      if (data) {
        this.currentStepIndex = data.currentStepIndex;
        this.completedSteps = new Set(data.completedSteps);
        this.lifecycle.transitionTo(data.status);
      }
    }
    // Public API helpers for steps
    getStepContext() {
      if (!this.config) {
        return {
          plugin: "",
          userId: 0,
          completeStep: () => {
          },
          skipStep: () => {
          },
          goNext: () => {
          },
          goBack: () => {
          },
          emit: () => {
          }
        };
      }
      return {
        plugin: this.config.plugin,
        userId: 0,
        // Placeholder, ideally passed in config or separate init
        completeStep: () => {
          const current = this.getCurrentStep();
          if (current) this.completeStep(current.id);
        },
        skipStep: () => {
          const current = this.getCurrentStep();
          if (current && current.canSkip) {
            tracker.emit("step_skipped", { stepId: current.id });
            this.completeStep(current.id);
          }
        },
        goNext: () => {
          const current = this.getCurrentStep();
          if (current && current.onNext) {
            Promise.resolve(current.onNext(this.getStepContext())).then((shouldProceed) => {
              if (shouldProceed !== false) {
                this.completeStep(current.id);
              }
            });
          } else if (current) {
            this.completeStep(current.id);
          }
        },
        goBack: () => {
          if (this.currentStepIndex > 0) {
            this.currentStepIndex--;
            this.lifecycle.transitionTo("step_in_progress");
            tracker.emit("step_changed", { index: this.currentStepIndex });
          }
        },
        emit: (event, payload) => tracker.emit(event, payload)
      };
    }
    getStatus() {
      return this.lifecycle.getStatus();
    }
    getProgress() {
      if (!this.config) return null;
      return {
        total: this.config.steps.length,
        current: this.currentStepIndex,
        percent: Math.round(this.completedSteps.size / this.config.steps.length * 100),
        steps: this.config.steps.map((step, index) => ({
          ...step,
          status: this.completedSteps.has(step.id) ? "completed" : index === this.currentStepIndex ? "current" : "pending"
        }))
      };
    }
  };
  var engine = new LinnoOnboardingEngine();

  // src/adapters/vanilla.ts
  var VanillaAdapter = class {
    constructor(containerId) {
      this.containerId = containerId;
    }
    init() {
      tracker.on("onboarding_started", () => this.render());
      tracker.on("step_completed", () => this.render());
      tracker.on("step_skipped", () => this.render());
    }
    render() {
      const container = document.getElementById(this.containerId);
      if (!container) return;
      const currentStep = engine.getCurrentStep();
      if (!currentStep) return;
      container.innerHTML = `<h2>${currentStep.title}</h2><p>${currentStep.description || ""}</p>`;
      if (currentStep.mount) {
        const stepContainer = document.createElement("div");
        container.appendChild(stepContainer);
        currentStep.mount(stepContainer, engine.getStepContext());
      }
      const nextBtn = document.createElement("button");
      nextBtn.innerText = "Next";
      nextBtn.onclick = () => {
        engine.getStepContext().goNext();
      };
      container.appendChild(nextBtn);
    }
  };

  // src/index.ts
  var registerOnboarding = async (config) => {
    await engine.register(config);
  };
  return __toCommonJS(src_exports);
})();
