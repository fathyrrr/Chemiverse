(function() {
    // Gracefully check if reduced motion is preferred
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        console.log('Reduced motion preferred: skipping WebGL background shader.');
        return;
    }

    // Insert canvas if not already in document
    let canvas = document.getElementById('bg-liquid-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'bg-liquid-canvas';
        // Insert at the absolute start of body so it sits behind page content flow
        document.body.insertBefore(canvas, document.body.firstChild);
    }

    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    if (!gl) {
        console.warn('WebGL is not supported on this browser. Falling back to static styling.');
        return;
    }

    // Vertex Shader: Standard 2D quad layout
    const vsSource = `
        attribute vec2 position;
        varying vec2 vUv;
        void main() {
            vUv = position * 0.5 + 0.5;
            gl_Position = vec4(position, 0.0, 1.0);
        }
    `;

    // Fragment Shader: 3D Domain-Warped Simplex Noise for smooth fluid flow
    const fsSource = `
        precision highp float;
        uniform float uTime;
        uniform vec2 uResolution;
        uniform bool uDark;
        varying vec2 vUv;

        // Description : Array and textureless GLSL 2D/3D/4D simplex noise functions.
        //      Author : Ian McEwan, Ashima Arts.
        //  Maintainer : stegu
        //     Lastmod : 20110822 (ijm)
        //     License : Copyright (C) 2011 Ashima Arts. All rights reserved.
        //               Distributed under the MIT License. See LICENSE file.
        //               https://github.com/ashima/webgl-noise
        //               https://github.com/stegu/webgl-noise

        vec3 mod289(vec3 x) {
            return x - floor(x * (1.0 / 289.0)) * 289.0;
        }

        vec4 mod289(vec4 x) {
            return x - floor(x * (1.0 / 289.0)) * 289.0;
        }

        vec4 permute(vec4 x) {
            return mod289(((x*34.0)+1.0)*x);
        }

        vec4 taylorInvSqrt(vec4 r) {
            return 1.79284291400159 - 0.85373472095314 * r;
        }

        float snoise(vec3 v) {
            const vec2 C = vec2(1.0/6.0, 1.0/3.0);
            const vec4 D = vec4(0.0, 0.5, 1.0, 2.0);

            vec3 i  = floor(v + dot(v, C.yyy));
            vec3 x0 =   v - i + dot(i, C.xxx);

            vec3 g = step(x0.yzx, x0.xyz);
            vec3 l = 1.0 - g;
            vec3 i1 = min(g.xyz, l.zxy);
            vec3 i2 = max(g.xyz, l.zxy);

            vec3 x1 = x0 - i1 + C.xxx;
            vec3 x2 = x0 - i2 + C.yyy;
            vec3 x3 = x0 - D.yyy;

            i = mod289(i);
            vec4 p = permute(permute(permute(
                         i.z + vec4(0.0, i1.z, i2.z, 1.0))
                       + i.y + vec4(0.0, i1.y, i2.y, 1.0))
                       + i.x + vec4(0.0, i1.x, i2.x, 1.0));

            float n_ = 0.142857142857;
            vec3  ns = n_ * D.wyz - D.xzx;

            vec4 j = p - 49.0 * floor(p * ns.z * ns.z);

            vec4 x_ = floor(j * ns.z);
            vec4 y_ = floor(j - 7.0 * x_);

            vec4 x = x_ *ns.x + ns.yyyy;
            vec4 y = y_ *ns.x + ns.yyyy;
            vec4 h = 1.0 - abs(x) - abs(y);

            vec4 b0 = vec4(x.xy, y.xy);
            vec4 b1 = vec4(x.zw, y.zw);

            vec4 s0 = floor(b0)*2.0 + 1.0;
            vec4 s1 = floor(b1)*2.0 + 1.0;
            vec4 sh = -step(h, vec4(0.0));

            vec4 a0 = b0.xzyw + s0.xzyw*sh.xxyy;
            vec4 a1 = b1.xzyw + s1.xzyw*sh.zzww;

            vec3 p0 = vec3(a0.xy,h.x);
            vec3 p1 = vec3(a0.zw,h.y);
            vec3 p2 = vec3(a1.xy,h.z);
            vec3 p3 = vec3(a1.zw,h.w);

            vec4 norm = taylorInvSqrt(vec4(dot(p0,p0), dot(p1,p1), dot(p2, p2), dot(p3,p3)));
            p0 *= norm.x;
            p1 *= norm.y;
            p2 *= norm.z;
            p3 *= norm.w;

            vec4 m = max(0.6 - vec4(dot(x0,x0), dot(x1,x1), dot(x2,x2), dot(x3,x3)), 0.0);
            m = m * m;
            return 42.0 * dot(m*m, vec4(dot(p0,x0), dot(p1,x1),
                                            dot(p2,x2), dot(p3,x3)));
        }

        // Fractal Brownian Motion (FBM) to layer the noise
        float fbm(vec3 p) {
            float value = 0.0;
            float amplitude = 0.5;
            float frequency = 1.0;
            for (int i = 0; i < 5; i++) {
                value += amplitude * snoise(p * frequency);
                amplitude *= 0.5;
                frequency *= 2.0;
            }
            return value;
        }

        void main() {
            vec2 uv = vUv;
            // Map uTime to a smooth flowing speed
            float t = uTime * 0.04;
            
            // Domain warping (noise inputs warping other noise coordinates)
            // This creates the organic "flowing water/liquid" aesthetic
            vec3 p = vec3(uv * 1.5, t);
            float noise = fbm(p + fbm(p + fbm(p)));
            noise = noise * 0.5 + 0.5; // map to [0, 1]

            vec3 colA, colB, colC;
            if (uDark) {
                // Smooth black and white combination tailored for dark interfaces:
                // Dark slate black & deep grey flow so foreground content stays readable
                colA = vec3(0.015, 0.015, 0.02); 
                colB = vec3(0.06, 0.06, 0.08); 
                colC = vec3(0.035, 0.035, 0.045); 
            } else {
                // Light theme fallback shades
                colA = vec3(0.98, 0.98, 0.98); 
                colB = vec3(0.88, 0.88, 0.92); 
                colC = vec3(0.94, 0.94, 0.96); 
            }

            // Mix colors using noise
            vec3 col = mix(colA, colB, smoothstep(0.2, 0.8, noise));
            col = mix(col, colC, smoothstep(0.5, 0.9, noise) * 0.35);

            // Soft vignette to slightly darken edges and keep center elements bright
            float vignette = 1.0 - length(uv - 0.5) * 0.6;
            col *= vignette;

            gl_FragColor = vec4(col, 1.0);
        }
    `;

    // Helper for compiling shaders
    function createShader(gl, type, source) {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            console.error('Error compiling shader:', gl.getShaderInfoLog(shader));
            gl.deleteShader(shader);
            return null;
        }
        return shader;
    }

    const vs = createShader(gl, gl.VERTEX_SHADER, vsSource);
    const fs = createShader(gl, gl.FRAGMENT_SHADER, fsSource);
    if (!vs || !fs) return;

    const program = gl.createProgram();
    gl.attachShader(program, vs);
    gl.attachShader(program, fs);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        console.error('Error linking WebGL program:', gl.getProgramInfoLog(program));
        return;
    }

    // Set up full screen quad coordinates
    const vertices = new Float32Array([
        -1, -1,
         1, -1,
        -1,  1,
        -1,  1,
         1, -1,
         1,  1
    ]);

    const buffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);

    const posAttr = gl.getAttribLocation(program, 'position');
    gl.enableVertexAttribArray(posAttr);
    gl.vertexAttribPointer(posAttr, 2, gl.FLOAT, false, 0, 0);

    const uTimeLoc = gl.getUniformLocation(program, 'uTime');
    const uResolutionLoc = gl.getUniformLocation(program, 'uResolution');
    const uDarkLoc = gl.getUniformLocation(program, 'uDark');

    // Resize Handler
    function handleResize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
        const w = window.innerWidth;
        const h = window.innerHeight;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        gl.viewport(0, 0, canvas.width, canvas.height);
    }
    window.addEventListener('resize', handleResize);
    handleResize();

    // Render loop
    const startTime = Date.now();
    let animFrameId = null;

    function render() {
        animFrameId = requestAnimationFrame(render);
        const elapsed = (Date.now() - startTime) / 1000;

        gl.useProgram(program);

        // Update uniforms
        gl.uniform1f(uTimeLoc, elapsed);
        gl.uniform2f(uResolutionLoc, canvas.width, canvas.height);

        // Check if dark mode is active (based on root class 'dark' or lack of 'light')
        const isDark = document.documentElement.classList.contains('dark') || 
                       !document.documentElement.classList.contains('light');
        gl.uniform1i(uDarkLoc, isDark ? 1 : 0);

        // Draw quad triangles
        gl.drawArrays(gl.TRIANGLES, 0, 6);
    }
    render();

    // Cleanup logic if script needs to be reloaded/re-initialized
    window._cleanupBgLiquidShader = function() {
        if (animFrameId) {
            cancelAnimationFrame(animFrameId);
        }
        window.removeEventListener('resize', handleResize);
        gl.deleteProgram(program);
        gl.deleteShader(vs);
        gl.deleteShader(fs);
        gl.deleteBuffer(buffer);
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
    };
})();
