import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const isDarkTheme = () => document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

const easeOutCubic = (value) => 1 - ((1 - value) ** 3);

const showFallback = (root, message = '') => {
    root.classList.remove('is-ready');
    root.classList.add('is-fallback');

    const status = root.querySelector('[data-showroom-status]');
    if (message && status) status.textContent = message;
};

const createMaterial = (THREE, options) => new THREE.MeshPhysicalMaterial(options);

const createProceduralCar = () => {
    const group = new THREE.Group();
    const chassis = new THREE.Group();
    group.add(chassis);

    const paint = createMaterial(THREE, {
        color: 0x174987,
        metalness: 0.88,
        roughness: 0.2,
        clearcoat: 1,
        clearcoatRoughness: 0.12,
    });
    const paintDark = createMaterial(THREE, {
        color: 0x09234a,
        metalness: 0.82,
        roughness: 0.24,
        clearcoat: 1,
        clearcoatRoughness: 0.16,
    });
    const glass = createMaterial(THREE, {
        color: 0x06111f,
        metalness: 0.12,
        roughness: 0.1,
        transmission: 0.2,
        transparent: true,
        opacity: 0.9,
    });
    const tire = new THREE.MeshStandardMaterial({ color: 0x05070b, metalness: 0.18, roughness: 0.72 });
    const rim = new THREE.MeshStandardMaterial({ color: 0x8fa5c0, metalness: 0.92, roughness: 0.24 });
    const trim = new THREE.MeshStandardMaterial({ color: 0x111a28, metalness: 0.58, roughness: 0.28 });
    const headlightMaterial = new THREE.MeshStandardMaterial({
        color: 0xd6e9ff,
        emissive: 0x2e6fd9,
        emissiveIntensity: 0.18,
        transparent: true,
        opacity: 0.22,
        roughness: 0.16,
        metalness: 0.14,
    });

    const addMesh = (geometry, material, position, scale = [1, 1, 1]) => {
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(...position);
        mesh.scale.set(...scale);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        chassis.add(mesh);
        return mesh;
    };

    addMesh(new THREE.BoxGeometry(4.35, 0.58, 1.82), paint, [0, 0.66, 0]);
    addMesh(new THREE.BoxGeometry(1.65, 0.2, 1.76), paint, [1.23, 0.98, 0]);
    addMesh(new THREE.BoxGeometry(0.18, 0.22, 1.72), paintDark, [2.08, 0.61, 0]);
    addMesh(new THREE.BoxGeometry(0.12, 0.2, 1.08), trim, [2.2, 0.62, 0]);
    addMesh(new THREE.SphereGeometry(1, 32, 18), paint, [-0.3, 1.08, 0], [1.52, 0.52, 0.84]);
    addMesh(new THREE.SphereGeometry(1, 32, 18), glass, [-0.28, 1.18, 0], [1.27, 0.34, 0.79]);
    addMesh(new THREE.BoxGeometry(2.5, 0.11, 0.06), trim, [-0.05, 1.58, 0]);
    addMesh(new THREE.BoxGeometry(3.5, 0.08, 0.06), paintDark, [-0.05, 0.42, 0.89]);
    addMesh(new THREE.BoxGeometry(3.5, 0.08, 0.06), paintDark, [-0.05, 0.42, -0.89]);

    const headlights = [];
    const headlightLights = [];
    [-1, 1].forEach((side) => {
        const lens = addMesh(new THREE.BoxGeometry(0.09, 0.18, 0.56), headlightMaterial, [2.14, 0.86, side * 0.55]);
        lens.rotation.y = side * -0.08;
        headlights.push({ material: headlightMaterial, mesh: lens });

        const light = new THREE.PointLight(0x7db1ff, 0, 3.4, 2);
        light.position.set(2.34, 0.85, side * 0.55);
        light.castShadow = false;
        chassis.add(light);
        headlightLights.push(light);
    });

    [-1, 1].forEach((side) => {
        const wheel = addMesh(new THREE.CylinderGeometry(0.45, 0.45, 0.28, 32), tire, [-1.35, 0.43, side * 0.94]);
        wheel.rotation.x = Math.PI / 2;
        const wheelCap = addMesh(new THREE.CylinderGeometry(0.24, 0.24, 0.3, 24), rim, [-1.35, 0.43, side * 0.95]);
        wheelCap.rotation.x = Math.PI / 2;
        const frontWheel = addMesh(new THREE.CylinderGeometry(0.45, 0.45, 0.28, 32), tire, [1.38, 0.43, side * 0.94]);
        frontWheel.rotation.x = Math.PI / 2;
        const frontCap = addMesh(new THREE.CylinderGeometry(0.24, 0.24, 0.3, 24), rim, [1.38, 0.43, side * 0.95]);
        frontCap.rotation.x = Math.PI / 2;
    });

    const stripe = addMesh(new THREE.BoxGeometry(0.035, 0.07, 0.9), new THREE.MeshStandardMaterial({ color: 0x8dbbff, emissive: 0x1d5db5, emissiveIntensity: 0.45 }), [1.72, 0.91, 0]);
    stripe.rotation.y = Math.PI / 2;

    group.scale.setScalar(0.92);
    group.position.y = 0.05;
    group.rotation.y = -0.28;

    return { group, headlights, headlightLights };
};

const normalizeExternalModel = (model) => {
    const bounds = new THREE.Box3().setFromObject(model);
    const size = bounds.getSize(new THREE.Vector3());
    const largest = Math.max(size.x, size.y, size.z) || 1;
    model.scale.setScalar(3.9 / largest);
    const normalizedBounds = new THREE.Box3().setFromObject(model);
    const center = normalizedBounds.getCenter(new THREE.Vector3());
    model.position.sub(center);
    model.position.y += 0.38;
    model.traverse((child) => {
        if (!child.isMesh) return;
        child.castShadow = true;
        child.receiveShadow = true;
    });
    return model;
};

export const initShowroomScene = (root) => {
    if (!root || root.dataset.mcShowroomReady === 'true' || !root.isConnected) return () => {};

    const host = root.querySelector('[data-showroom-canvas]');
    if (!host) return () => {};

    root.dataset.mcShowroomReady = 'loading';
    const coarsePointer = window.matchMedia('(pointer: coarse)').matches;
    const canvas = document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    canvas.className = 'mc-showroom__webgl';
    host.append(canvas);

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({
            canvas,
            alpha: true,
            antialias: !coarsePointer,
            powerPreference: 'high-performance',
        });
    } catch {
        showFallback(root, 'Modo visual estático ativado neste dispositivo.');
        root.dataset.mcShowroomReady = 'false';
        return () => {};
    }

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 40);
    const cameraStart = new THREE.Vector3(6.6, 3.15, 7.8);
    const cameraEnd = new THREE.Vector3(5.1, 2.35, 6.25);
    camera.position.copy(cameraStart);

    const hemiLight = new THREE.HemisphereLight(0xb8d1f6, 0x0b1220, 0.72);
    scene.add(hemiLight);
    const keyLight = new THREE.DirectionalLight(0xf4f8ff, 2.1);
    keyLight.position.set(4, 7, 5);
    keyLight.castShadow = !coarsePointer;
    keyLight.shadow.mapSize.set(coarsePointer ? 512 : 1024, coarsePointer ? 512 : 1024);
    keyLight.shadow.camera.near = 1;
    keyLight.shadow.camera.far = 18;
    scene.add(keyLight);
    const rimLight = new THREE.DirectionalLight(0x286ae3, 1.55);
    rimLight.position.set(-4, 2.5, -5);
    scene.add(rimLight);

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(8, 64),
        new THREE.MeshStandardMaterial({ color: 0x0b1220, metalness: 0.5, roughness: 0.3 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = 0;
    floor.receiveShadow = true;
    scene.add(floor);

    const car = createProceduralCar();
    scene.add(car.group);

    const controls = new OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan = false;
    controls.enableZoom = !coarsePointer;
    controls.minDistance = 4.25;
    controls.maxDistance = 8.3;
    controls.minPolarAngle = Math.PI * 0.27;
    controls.maxPolarAngle = Math.PI * 0.48;
    controls.target.set(0, 0.72, 0);
    controls.saveState();
    controls.enabled = false;
    canvas.style.touchAction = coarsePointer ? 'pan-y' : 'none';

    const modelUrl = root.dataset.modelUrl?.trim();
    if (modelUrl) {
        const loader = new GLTFLoader();
        loader.load(modelUrl, (gltf) => {
            if (!root.isConnected) return;
            const externalModel = normalizeExternalModel(gltf.scene);
            scene.remove(car.group);
            scene.add(externalModel);
            root.dataset.mcShowroomModel = 'gltf';
        }, undefined, () => {
            root.dataset.mcShowroomModel = 'procedural-fallback';
        });
    } else {
        root.dataset.mcShowroomModel = 'procedural-original';
    }

    const status = root.querySelector('[data-showroom-status]');
    const reset = root.querySelector('[data-showroom-reset]');
    const fallback = root.querySelector('[data-showroom-fallback]');
    const initialStatus = status?.textContent || 'Arraste para explorar';
    const themeState = { dark: isDarkTheme(), targetLight: 0.12, targetOpacity: 0.24 };
    let disposed = false;
    let active = true;
    let frame = 0;
    let introStart = performance.now();
    let introFinished = false;
    let needsRender = true;
    let idleFrameCount = 0;

    const syncTheme = () => {
        themeState.dark = isDarkTheme();
        themeState.targetLight = themeState.dark ? 0.12 : 2.65;
        themeState.targetOpacity = themeState.dark ? 0.28 : 1;
        const background = themeState.dark ? 0x050912 : 0x172641;
        scene.background = new THREE.Color(background);
        scene.fog = new THREE.Fog(background, 8, 16);
        hemiLight.intensity = themeState.dark ? 0.46 : 1.12;
        keyLight.intensity = themeState.dark ? 1.35 : 2.45;
        rimLight.intensity = themeState.dark ? 1.15 : 1.8;
        floor.material.color.set(themeState.dark ? 0x080d16 : 0x18273f);
        needsRender = true;
    };
    syncTheme();

    const resize = () => {
        const bounds = host.getBoundingClientRect();
        const width = Math.max(1, bounds.width);
        const height = Math.max(1, bounds.height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, coarsePointer ? 1 : 1.5));
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        needsRender = true;
    };

    const animateHeadlights = () => {
        let animating = false;
        car.headlights.forEach(({ material }) => {
            animating ||= Math.abs(material.opacity - themeState.targetOpacity) > 0.01;
            animating ||= Math.abs(material.emissiveIntensity - (themeState.dark ? 0.18 : 2.4)) > 0.03;
            material.opacity = THREE.MathUtils.damp(material.opacity, themeState.targetOpacity, 7, 1 / 60);
            material.emissiveIntensity = THREE.MathUtils.damp(material.emissiveIntensity, themeState.dark ? 0.18 : 2.4, 7, 1 / 60);
        });
        car.headlightLights.forEach((light) => {
            animating ||= Math.abs(light.intensity - themeState.targetLight) > 0.02;
            light.intensity = THREE.MathUtils.damp(light.intensity, themeState.targetLight, 6, 1 / 60);
        });
        return animating;
    };

    const tick = (now) => {
        frame = 0;
        if (disposed || !active || !root.isConnected) return;

        if (!introFinished) {
            const progress = Math.min((now - introStart) / 1550, 1);
            const eased = easeOutCubic(progress);
            camera.position.lerpVectors(cameraStart, cameraEnd, eased);
            car.group.position.y = THREE.MathUtils.lerp(-0.38, 0.05, eased);
            car.group.rotation.y = THREE.MathUtils.lerp(-0.62, -0.28, eased);
            if (progress >= 1) {
                introFinished = true;
                controls.enabled = true;
                if (status) status.textContent = initialStatus;
            }
        }

        const headlightsAnimating = animateHeadlights();
        const controlsChanged = controls.update();
        renderer.render(scene, camera);
        const shouldContinue = !introFinished || headlightsAnimating || controlsChanged || needsRender;
        needsRender = false;
        if (shouldContinue || idleFrameCount < 1) {
            idleFrameCount = shouldContinue ? 0 : idleFrameCount + 1;
            frame = window.requestAnimationFrame(tick);
        }
    };

    const start = () => {
        idleFrameCount = 0;
        if (!frame && active && !disposed) frame = window.requestAnimationFrame(tick);
    };

    controls.addEventListener('change', () => {
        needsRender = true;
        start();
    });

    const intersection = 'IntersectionObserver' in window
        ? new IntersectionObserver(([entry]) => {
            active = entry.isIntersecting;
            if (active) start();
            else if (frame) {
                window.cancelAnimationFrame(frame);
                frame = 0;
            }
        }, { threshold: 0.05 })
        : null;
    intersection?.observe(root);

    const resizeObserver = 'ResizeObserver' in window ? new ResizeObserver(resize) : null;
    resizeObserver?.observe(host);
    window.addEventListener('resize', resize, { passive: true });

    const themeObserver = new MutationObserver(() => {
        syncTheme();
        start();
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    reset?.addEventListener('click', () => {
        controls.reset();
        if (status) status.textContent = 'Vista recentrada';
        window.setTimeout(() => {
            if (status && root.isConnected) status.textContent = initialStatus;
        }, 1200);
    });

    canvas.addEventListener('webglcontextlost', (event) => {
        event.preventDefault();
        active = false;
        if (frame) window.cancelAnimationFrame(frame);
        showFallback(root, 'WebGL indisponível; modo visual estático ativado.');
    }, { passive: false });

    root.classList.add('is-ready');
    root.classList.remove('is-fallback');
    fallback?.setAttribute('aria-hidden', 'true');
    resize();
    start();

    const cleanup = () => {
        disposed = true;
        if (frame) window.cancelAnimationFrame(frame);
        intersection?.disconnect();
        resizeObserver?.disconnect();
        themeObserver.disconnect();
        window.removeEventListener('resize', resize);
        controls.dispose();
        renderer.dispose();
    };

    root.dataset.mcShowroomReady = 'true';
    root._mcShowroomCleanup = cleanup;
    return cleanup;
};

